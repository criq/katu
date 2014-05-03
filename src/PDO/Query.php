<?php

namespace Katu\PDO;

use \PDO;

class Query {

	public $pdo;
	public $sql;
	public $params = array();
	public $meta   = array();

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

	public function setMeta($meta) {
		$this->meta[] = $meta;
	}

	public function getStatement() {
		$statement = $this->pdo->connection->prepare($this->sql);

		foreach ($this->params as $param => $value) {
			if (is_int($value)) {
				$statement->bindValue($param, $value, PDO::PARAM_INT);
			} else {
				$statement->bindValue($param, $value);
			}
		}

		return $statement;
	}

	public function getResult() {
		return new Result($this->pdo, $this->getStatement(), $this->params, $this->meta);
	}

	public function getClassResult($class) {
		$result = $this->getResult();
		$result->setClass($class);

		return $result;
	}

}
