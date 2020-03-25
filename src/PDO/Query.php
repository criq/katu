<?php

namespace Katu\PDO;

class Query
{
	public $className;
	public $connection;
	public $page;
	public $sql;
	public $values = [];

	public function __construct(Connection $connection, $sql, array $values = [])
	{
		$this->connection = $connection;

		if ($sql instanceof \Sexy\Expression) {
			$this->sql = $sql->getSql();
			$this->values = $sql->getValues();
			$page = $sql->getPage();
			if ($page) {
				$this->setPage($page);
			}
		} else {
			$this->sql = $sql;
			$this->values = $values;
		}
	}

	public function setSql($sql)
	{
		return $this->sql = $sql;
	}

	public function setValue($name, $value)
	{
		return $this->values[$name] = $value;
	}

	public function setValues($values)
	{
		return $this->values = array_merge($this->values, $values);
	}

	public function setPage($page)
	{
		return $this->page = $page;
	}

	public function setClassName(\Katu\Tools\Classes\ClassName $className)
	{
		return $this->className = $className;
	}

	public function getStatement()
	{
		$statement = $this->connection->connection->prepare($this->sql);

		foreach ($this->values as $name => $value) {
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
