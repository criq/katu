<?php

namespace Katu\PDO;

class Query
{
	protected $connection;
	protected $factory;
	protected $page;
	protected $params = [];
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

	public function getResult()
	{
		$factory = $this->getFactory();
		if (!$factory) {
			$factory = new \Katu\Tools\Factories\ArrayFactory;
		}

		$statement = $this->getStatement();
		$statement->execute();

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

		if ($this->getPage() && !is_null($foundRows)) {
			$pagination = new \Katu\Types\TPagination($foundRows, $this->getPage()->getPerPage(), $this->getPage()->getPage());
			$result = new \Katu\PDO\Results\PaginatedResult($this->getConnection(), $statement, $factory, $pagination);
		} else {
			$result = new \Katu\PDO\Results\Result($this->getConnection(), $statement, $factory);
		}

		return $result;
	}
}
