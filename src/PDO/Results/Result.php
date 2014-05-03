<?php

namespace Katu\PDO\Results;

use \PDO;

class Result {

	public $pdo;
	public $statement;

	public function __construct($pdo, $statement) {
		$this->pdo       = $pdo;
		$this->statement = $statement;

		$this->statement->execute();

		if ((int) $this->statement->errorCode()) {
			$error = $this->statement->errorInfo();
			throw new \Exception($error[2], $error[1]);
		}
	}

	// Pagination.
	public function getCount() {
		return count($this->statement);
	}

	// Arrays.
	public function getArray() {
		return $this->statement->fetchAll(PDO::FETCH_NUM);
	}

	public function getAssoc() {
		return $this->statement->fetchAll(PDO::FETCH_ASSOC);
	}

}
