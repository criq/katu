<?php

namespace Katu\Pdo;

use \PDO;

class Query {

	public $pdo;
	public $sql;
	public $bindValues = [];
	public $page;
	public $class;

	public function __construct(Connection $pdo, $sql = '', $bindValues = []) {
		$this->pdo = $pdo;
		$this->sql = $sql;
		$this->bindValues = $bindValues;
	}

	public function setSql($sql) {
		return $this->sql = $sql;
	}

	public function setFromSql(\Sexy\Expression $sql) {
		$this->sql = $sql->getSql();
		$this->bindValues = $sql->getBindValues();

		$page = $sql->getPage();
		if ($page) {
			$this->setPage($page);
		}
	}

	public function setBindValue($bindValue, $value) {
		return $this->bindValues[$bindValue] = $value;
	}

	public function setBindValues($bindValues) {
		return $this->bindValues = array_merge($this->bindValues, $bindValues);
	}

	public function setPage($page) {
		return $this->page = $page;
	}

	public function setClass($class) {
		return $this->class = $class;
	}

	public function getStatement() {
		$statement = $this->pdo->connection->prepare($this->sql);

		foreach ($this->bindValues as $bindValue => $value) {
			if (is_int($value)) {
				$statement->bindValue($bindValue, $value, PDO::PARAM_INT);
			} elseif (is_float($value)) {
				$statement->bindValue($bindValue, $value, PDO::PARAM_STR);
			} else {
				$statement->bindValue($bindValue, $value, PDO::PARAM_STR);
			}
		}

		return $statement;
	}

	public function getResult() {
		$stopwatch = new \Katu\Utils\Stopwatch();

		if ($this->class) {
			$result = new Results\ClassResult($this->pdo, $this->getStatement(), $this->page, $this->class);
		} elseif ($this->page) {
			$result = new Results\PaginatedResult($this->pdo, $this->getStatement(), $this->page);
		} else {
			$result = new Results\Result($this->pdo, $this->getStatement());
		}

		\Katu\Utils\Profiler::add(new \Katu\Utils\Profiler\Query($this, $stopwatch));

		return $result;
	}

}
