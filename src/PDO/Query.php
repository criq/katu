<?php

namespace Katu\PDO;

use \PDO;

class Query {

	public $pdo;
	public $sql;
	public $params = array();

	public $page;
	public $class;

	public function __construct($pdo, $sql = "", $params = array()) {
		$this->pdo = $pdo;
		$this->sql = $sql;
		$this->params = $params;
	}

	public function setSQL($sql) {
		$this->sql = $sql;
	}

	public function setParam($param, $value) {
		$this->params[$param] = $value;
	}

	public function setParams($params) {
		$this->params = array_merge($this->params, $params);
	}

	public function setPage($page) {
		$this->page = $page;
	}

	public function setClass($class) {
		$this->class = $class;
	}

	public function getStatement() {
		$statement = $this->pdo->connection->prepare($this->sql);

		foreach ($this->params as $param => $value) {
			if (is_int($value)) {
				$statement->bindValue($param, $value, PDO::PARAM_INT);
			} else {
				$statement->bindValue($param, $value, PDO::PARAM_STR);
			}
		}

		return $statement;
	}

	public function getResult() {
		if ($this->class) {
			return new Results\ClassResult    ($this->pdo, $this->getStatement(), $this->page, $this->class);
		} elseif ($this->page) {
			return new Results\PaginatedResult($this->pdo, $this->getStatement(), $this->page);
		} else {
			return new Results\Result         ($this->pdo, $this->getStatement());
		}
	}

}
