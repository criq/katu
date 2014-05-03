<?php

namespace Katu\PDO\Results;

use \PDO;

class Result implements \Iterator {

	public $pdo;
	public $statement;

	protected $position = 0;
	protected $iteratorArray;

	public function __construct($pdo, $statement) {
		$this->pdo       = $pdo;
		$this->statement = $statement;

		$this->statement->execute();

		if ((int) $this->statement->errorCode()) {
			$error = $this->statement->errorInfo();
			throw new \Exception($error[2], $error[1]);
		}
	}

	public function getCount() {
		return count($this->statement);
	}

	public function getArray() {
		return $this->statement->fetchAll(PDO::FETCH_ASSOC);
	}

	public function setIteratorArray() {
		if (is_null($this->iteratorArray)) {
			$this->iteratorArray = $this->getArray();
		}
	}

	public function rewind() {
		$this->position = 0;
	}

	public function current() {
		$this->setIteratorArray();

		return $this->iteratorArray[$this->position];
	}

	public function key() {
		return $this->position;
	}

	public function next() {
		++$this->position;
	}

	public function valid() {
		$this->setIteratorArray();

		return isset($this->iteratorArray[$this->position]);
	}

}
