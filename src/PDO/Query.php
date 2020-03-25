<?php

namespace Katu\PDO;

class Query
{
	public $bindValues = [];
	public $className;
	public $page;
	public $pdo;
	public $sql;

	public function __construct(Connection $pdo, $sql, array $bindValues = [])
	{
		$this->pdo = $pdo;

		if ($sql instanceof \Sexy\Expression) {
			$this->sql = $sql->getSql();
			$this->bindValues = $sql->getBindValues();
			$page = $sql->getPage();
			if ($page) {
				$this->setPage($page);
			}
		} else {
			$this->sql = $sql;
			$this->bindValues = $bindValues;
		}
	}

	public function setSql($sql)
	{
		return $this->sql = $sql;
	}

	public function setBindValue($bindValue, $value)
	{
		return $this->bindValues[$bindValue] = $value;
	}

	public function setBindValues($bindValues)
	{
		return $this->bindValues = array_merge($this->bindValues, $bindValues);
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
		$statement = $this->pdo->connection->prepare($this->sql);

		foreach ($this->bindValues as $bindValue => $value) {
			if (is_string($value)) {
				$statement->bindValue($bindValue, $value, \PDO::PARAM_STR);
			} elseif (is_int($value)) {
				$statement->bindValue($bindValue, $value, \PDO::PARAM_INT);
			} elseif (is_float($value)) {
				$statement->bindValue($bindValue, $value, \PDO::PARAM_STR);
			} else {
				$statement->bindValue($bindValue, $value, \PDO::PARAM_STR);
			}
		}

		return $statement;
	}

	public function getResult()
	{
		$stopwatch = new \Katu\Tools\Profiler\Stopwatch;

		if ($this->className) {
			$result = new Results\ClassResult($this->pdo, $this->getStatement(), $this->page, $this->className);
		} elseif ($this->page) {
			$result = new Results\PaginatedResult($this->pdo, $this->getStatement(), $this->page);
		} else {
			$result = new Results\Result($this->pdo, $this->getStatement());
		}

		\Katu\Tools\Profiler\Profiler::add(new \Katu\Tools\Profiler\Query($this, $stopwatch));

		return $result;
	}
}
