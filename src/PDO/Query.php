<?php

namespace Katu\PDO;

class Query
{
	public $className;
	public $connection;
	public $page;
	public $params = [];
	public $sql;

	public function __construct(Connection $connection, string $sql, array $params = [])
	{
		$this->connection = $connection;
		$this->params = $params;
		$this->sql = $sql;
	}

	public function setSql(string $sql) : Query
	{
		$this->sql = $sql;

		return $this;
	}

	public function setParam($name, $value) : Query
	{
		$this->params[$name] = $value;

		return $this;
	}

	public function setParams($params) : Query
	{
		$this->params = array_merge($this->params, $params);

		return $this;
	}

	public function setPage($page) : Query
	{
		$this->page = $page;

		return $this;
	}

	public function setClassName(\Katu\Tools\Classes\ClassName $className) : Query
	{
		$this->className = $className;

		return $this;
	}

	public function getStatement()
	{
		$statement = $this->connection->connection->prepare($this->sql);

		foreach ($this->params as $name => $value) {
			if (is_string($value)) {
				$statement->bindValue($name, $value, \PDO::PARAM_STR);
			} elseif (is_int($value)) {
				$statement->bindValue($name, $value, \PDO::PARAM_INT);
			} elseif (is_float($value)) {
				$statement->bindValue($name, $value, \PDO::PARAM_STR);
			} else {
				$statement->bindValue($name, $value, \PDO::PARAM_STR);
			}
		}

		return $statement;
	}

	public function getResult()
	{
		$stopwatch = new \Katu\Tools\Profiler\Stopwatch;

		if ($this->className) {
			$result = new Results\ClassResult($this->connection, $this->getStatement(), $this->page, $this->className);
		} elseif ($this->page) {
			$result = new Results\PaginatedResult($this->connection, $this->getStatement(), $this->page);
		} else {
			$result = new Results\Result($this->connection, $this->getStatement());
		}

		\Katu\Tools\Profiler\Profiler::add(new \Katu\Tools\Profiler\Query($this, $stopwatch));

		return $result;
	}
}
