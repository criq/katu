<?php

namespace Katu\PDO;

class Query
{
	protected $connection;
	protected $factory;
	protected $page;
	protected $params = [];
	protected $result;
	protected $sql;
	protected $statement;

	public function __construct(Connection $connection, $sql, ?array $params = [])
	{
		$this->setConnection($connection);
		$this->setParams($params);
		$this->setSql($sql);
	}

	public function setConnection(Connection $connection) : Query
	{
		$this->connection = $connection;

		return $this;
	}

	public function getConnection() : Connection
	{
		return $this->connection;
	}

	public function setSql($sql) : Query
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

	public function setParam(string $name, $value) : Query
	{
		$this->params[$name] = $value;

		return $this;
	}

	public function setParams(?array $params = []) : Query
	{
		$this->params = array_merge($this->params, $params);

		return $this;
	}

	public function getParams() : array
	{
		return $this->params;
	}

	public function setPage(\Sexy\Page $page) : Query
	{
		$this->page = $page;

		return $this;
	}

	public function getPage() : ?\Sexy\Page
	{
		return $this->page;
	}

	public function setFactory(\Katu\Interfaces\Factory $factory) : Query
	{
		$this->factory = $factory;

		return $this;
	}

	public function getFactory() : ?\Katu\Interfaces\Factory
	{
		if (!$this->factory) {
			$this->factory = new \Katu\Tools\Factories\ArrayFactory;
		}

		return $this->factory;
	}

	public function getStatement() : \PDOStatement
	{
		if (!$this->statement) {
			$this->statement = $this->getConnection()->getConnection()->prepare($this->getSql());

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

	public function setResult(Result $result) : Query
	{
		$this->result = $result;

		return $this;
	}

	public function getResult() : Result
	{
		if (!$this->result) {
			$statement = $this->getStatement();
			$statement->execute();

			$errorInfo = $statement->errorInfo();
			if ((int)$errorInfo[1]) {
				$exception = new \Katu\Exceptions\Exception($errorInfo[2], $errorInfo[1]);

				// Table doesn't exist.
				if ($errorInfo[1] == 1146 && preg_match("/Table '(.+)\.(?<tableName>.+)' doesn't exist/", $errorInfo[2], $match)) {
					// Create the table.
					$sqlFile = new \Katu\Files\File(__DIR__, '..', '..', 'Tools', 'SQL', $match['tableName'] . '.create.sql');
					if ($sqlFile->exists()) {
						// There is a file, let's create the table.
						$this->getConnection()->createQuery($sqlFile->get())->getResult();
					} else {
						throw $exception;
					}
				} else {
					throw $exception;
				}
			}

			$foundRows = null;
			try {
				if (mb_strpos($statement->queryString, 'SQL_CALC_FOUND_ROWS') !== false) {
					$sql = " SELECT FOUND_ROWS() AS total ";
					$foundRowsQuery = $this->getConnection()->createQuery($sql);
					$foundRowsStatement = $foundRowsQuery->getStatement();
					$foundRowsStatement->execute();
					$fetched = $foundRowsStatement->fetchAll(\PDO::FETCH_ASSOC);
					$foundRows = (int)$fetched[0]['total'];
				}
			} catch (\Throwable $e) {
				// Nevermind.
			}

			$result = new Result($this);

			if ($this->getPage() && !is_null($foundRows)) {
				$result->setPagination(new \Katu\Types\TPagination($foundRows, $this->getPage()->getPerPage(), $this->getPage()->getPage()));
			} else {
				$rowCount = $statement->rowCount();
				$result->setPagination(new \Katu\Types\TPagination($rowCount, $rowCount ?: 1, 1));
			}

			foreach ($this->getStatement()->fetchAll(\PDO::FETCH_ASSOC) as $row) {
				$result->append($this->getFactory()->create($row));
			}

			$this->setResult($result);
		}

		return $this->result;
	}
}
