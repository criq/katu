<?php

namespace Katu\PDO;

use Katu\Types\TSeconds;

class Query
{
	protected $connection;
	protected $duration;
	protected $factory;
	protected $foundRows;
	protected $page;
	protected $params = [];
	protected $result;
	protected $sql;
	protected $statement;
	protected $statementDump;

	public function __construct(Connection $connection, $sql, ?array $params = [])
	{
		$this->setConnection($connection);
		$this->setParams($params);
		$this->setSql($sql);
	}

	public function setConnection(Connection $connection): Query
	{
		$this->connection = $connection;

		return $this;
	}

	public function getConnection(): Connection
	{
		return $this->connection;
	}

	public function setSql($sql): Query
	{
		$this->sql = $sql;
		if ($sql instanceof \Sexy\Select && $sql->getPage()) {
			$this->setPage($sql->getPage());
		}

		return $this;
	}

	public function getSql()
	{
		return $this->sql;
	}

	public function setParam(string $name, $value): Query
	{
		$this->params[$name] = $value;

		return $this;
	}

	public function setParams(?array $params = []): Query
	{
		$this->params = array_merge($this->params, $params);

		return $this;
	}

	public function getParams(): array
	{
		return $this->params;
	}

	public function setPage(\Sexy\Page $page): Query
	{
		$this->page = $page;

		return $this;
	}

	public function getPage(): ?\Sexy\Page
	{
		return $this->page;
	}

	public function setFactory(\Katu\Interfaces\Factory $factory): Query
	{
		$this->factory = $factory;

		return $this;
	}

	public function getFactory(): ?\Katu\Interfaces\Factory
	{
		if (!$this->factory) {
			$this->factory = new \Katu\Tools\Factories\ArrayFactory;
		}

		return $this->factory;
	}

	public function getStatement(): \PDOStatement
	{
		if (!$this->statement) {
			$this->statement = $this->getConnection()->getPdo()->prepare($this->getSql());

			foreach ($this->getParams() as $name => $value) {
				if (is_string($value)) {
					$this->statement->bindValue($name, $value, \PDO::PARAM_STR);
				} elseif (is_int($value)) {
					$this->statement->bindValue($name, $value, \PDO::PARAM_INT);
				} elseif (is_float($value)) {
					$this->statement->bindValue($name, $value, \PDO::PARAM_STR);
				} else {
					$this->statement->bindValue($name, $value, \PDO::PARAM_STR);
				}
			}
		}

		return $this->statement;
	}

	public function setStatementDump(StatementDump $statementDump): Query
	{
		$this->statementDump = $statementDump;

		return $this;
	}

	public function getStatementDump(): StatementDump
	{
		return $this->statementDump;
	}

	public function setDuration(TSeconds $duration): Query
	{
		$this->duration = $duration;

		return $this;
	}

	public function getDuration(): ?TSeconds
	{
		return $this->duration;
	}

	public function setFoundRows(int $foundRows): Query
	{
		$this->foundRows = $foundRows;

		return $this;
	}

	public function getFoundRows(): ?int
	{
		return $this->foundRows;
	}

	public function setResult(Result $result): Query
	{
		$this->result = $result;

		return $this;
	}

	public function getResult(): Result
	{
		if (!$this->result) {
			$statement = $this->getStatement();

			// Run the query.
			try {
				$stopwatch = new \Katu\Tools\Profiler\Stopwatch;
				$statement->execute();
				$this->setDuration(new TSeconds($stopwatch->getDuration()));
			} catch (\Throwable $e) {
				// Nevermind.
			} finally {
				$error = Exception::createFromErrorInfo($statement->errorInfo());
			}

			if ($error->getCode() == 1146 && preg_match("/Table '(.+)\.(?<tableName>.+)' doesn't exist/", $error->getMessage(), $match)) {
				// Create the table.
				$sqlFile = new \Katu\Files\File(__DIR__, '..', '..', 'Tools', 'SQL', $match['tableName'] . '.create.sql');
				if ($sqlFile->exists()) {
					// There is a file, let's create the table.
					$this->getConnection()->createQuery($sqlFile->get())->getResult();

					// Re-run the query.
					try {
						$stopwatch = new \Katu\Tools\Profiler\Stopwatch;
						$statement->execute();
						$this->setDuration(new TSeconds($stopwatch->getDuration()));
					} catch (\Throwable $e) {
						// Nevermind.
					} finally {
						$error = Exception::createFromErrorInfo($statement->errorInfo());
					}
				}
			}

			// Statement dump.
			ob_start();
			$statement->debugDumpParams();
			$this->setStatementDump(new StatementDump(ob_get_contents()));
			ob_end_clean();

			try {
				if (\Katu\Config\Config::get('app', 'profiler', 'pdo')) {
					$file = (\Katu\Files\File::createTemporaryWithFileName($this->getConnection()->getSessionId() . '.csv'));
					$csv = new \Katu\Files\Formats\CSV($file);
					$csv->append([
						preg_replace('/\./', ',', $this->getDuration()),
						$this->getStatementDump()->getSentSQL() ?: $this->statement->queryString,
					]);
				}
			} catch (\Katu\Exceptions\MissingConfigException $e) {
				// Nevermind.
			}

			// Found rows.
			try {
				if (mb_strpos($statement->queryString, 'SQL_CALC_FOUND_ROWS') !== false) {
					$sql = " SELECT FOUND_ROWS() AS total ";
					$foundRowsQuery = $this->getConnection()->createQuery($sql);
					$foundRowsStatement = $foundRowsQuery->getStatement();
					$foundRowsStatement->execute();
					$fetched = $foundRowsStatement->fetchAll(\PDO::FETCH_ASSOC);
					$this->setFoundRows((int)$fetched[0]['total']);
				}
			} catch (\Throwable $e) {
				// Nevermind.
			}

			// Result.
			$result = new Result($this);
			if ($error->getCode()) {
				$result->setError($error);
			}

			// Pagination.
			if ($this->getPage() && !is_null($this->getFoundRows())) {
				$result->setPagination(new \Katu\Types\TPagination($this->getFoundRows(), $this->getPage()->getPerPage(), $this->getPage()->getPage()));
			} else {
				$rowCount = $statement->rowCount();
				$result->setPagination(new \Katu\Types\TPagination($rowCount, $rowCount ?: 1, $this->getPage() ? $this->getPage()->getPage() : 1));
			}

			// Items.
			foreach ($this->getStatement()->fetchAll(\PDO::FETCH_ASSOC) as $row) {
				$result->append($this->getFactory()->create($row));
			}

			$this->setResult($result);
		}

		return $this->result;
	}
}
