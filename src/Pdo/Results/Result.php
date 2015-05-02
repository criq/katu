<?php

namespace Katu\Pdo\Results;

use \PDO;

class Result implements \Iterator, \ArrayAccess {

	public $pdo;
	public $statement;

	protected $_position      = 0;
	protected $_iteratorArray = null;
	protected $_array         = null;

	public function __construct($pdo, $statement) {
		$this->pdo       = $pdo;
		$this->statement = $statement;

		try {

			$this->statement->execute();

			if ((int) $this->statement->errorCode()) {
				$error = $this->statement->errorInfo();
				throw new \Exception($error[2], $error[1]);
			}

		} catch (\Exception $e) {

			// Non-existing table.
			if ($e->getCode() == 1146 && preg_match('#^Table \'(.+)\.(?<table>.+)\' doesn\'t exist$#', $e->getMessage(), $match)) {

				// Create the table.
				$sqlFileName = __DIR__ . '/../../Sql/' . $match['table'] . '.create.sql';
				if (file_exists(realpath($sqlFileName))) {

					// There is a file, let's create the table.
					$createQuery = $this->pdo->createQuery(file_get_contents($sqlFileName));
					$createQuery->getResult();

					$this->statement->execute();

					if ((int) $this->statement->errorCode()) {
						$error = $this->statement->errorInfo();
						throw new \Exception($error[2], $error[1]);
					}

				} else {
					throw $e;
				}

			} else {
				throw $e;
			}

		}

	}

	public function getCount() {
		$this->setArray();

		return count($this->_array);
	}

	public function getTotal() {
		return $this->getCount();
	}

	public function setArray() {
		if (is_null($this->_array)) {
			$this->_array = $this->statement->fetchAll(PDO::FETCH_ASSOC);
		}

		return $this->_array;
	}

	public function getArray() {
		$this->setArray();

		return $this->_array;
	}

	public function getColumnValues($column) {
		$values = array();

		foreach ($this as $row) {
			if (is_object($row)) {
				$values[] = $row->$column;
			} else {
				$values[] = $row[$column];
			}
		}

		return $values;
	}

	public function setIteratorArray() {
		if (is_null($this->_iteratorArray)) {
			$this->_iteratorArray = $this->getArray();
		}
	}

	public function rewind() {
		$this->_position = 0;
	}

	public function current() {
		$this->setIteratorArray();

		return $this->_iteratorArray[$this->_position];
	}

	public function key() {
		return $this->_position;
	}

	public function next() {
		++$this->_position;
	}

	public function valid() {
		$this->setIteratorArray();

		return isset($this->_iteratorArray[$this->_position]);
	}

	public function offsetSet($offset, $value) {
		$this->setIteratorArray();

		if (is_null($offset)) {
			$this->_iteratorArray[] = $value;
		} else {
			$this->_iteratorArray[$offset] = $value;
		}
	}

	public function offsetExists($offset) {
		$this->setIteratorArray();

		return isset($this->_iteratorArray[$offset]);
	}

	public function offsetUnset($offset) {
		$this->setIteratorArray();

		unset($this->_iteratorArray[$offset]);
	}

	public function offsetGet($offset) {
		$this->setIteratorArray();

		return isset($this->_iteratorArray[$offset]) ? $this->_iteratorArray[$offset] : null;
	}

}
