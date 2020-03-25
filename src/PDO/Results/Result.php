<?php

namespace Katu\PDO\Results;

use \PDO;

class Result implements \Iterator, \ArrayAccess
{
	protected $iteratorArray = null;
	protected $iteratorPosition = 0;
	public $pdo;
	public $statement;

	public function __construct($pdo, $statement)
	{
		$this->pdo = $pdo;
		$this->statement = $statement;

		try {
			$this->statement->execute();

			if (!preg_match("/^0+$/", $this->statement->errorCode())) {
				$error = $this->statement->errorInfo();
				throw new \Exception(implode(';', [
					$error[2],
					$this->statement->queryString,
				]), $error[1]);
			}
		} catch (\Exception $e) {
			// Non-existing table.
			if ($e->getCode() == 1146 && preg_match('/Table \'(.+)\.(?<table>.+)\' doesn\'t exist/', $e->getMessage(), $match)) {
				// Create the table.
				$sqlFile = new \Katu\Files\File(__DIR__, '..', '..', 'Tools', 'SQL', $match['table'] . '.create.sql');
				if ($sqlFile->exists()) {
					// There is a file, let's create the table.
					$createQuery = $this->pdo->createQuery($sqlFile->get());
					$createQuery->getResult();

					$this->statement->execute();

					if ((int)$this->statement->errorCode()) {
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

	public function getCount()
	{
		$this->setIteratorArray();

		return count($this->iteratorArray);
	}

	public function getTotal()
	{
		return $this->getCount();
	}

	public function getArray()
	{
		$this->setIteratorArray();

		return $this->iteratorArray;
	}

	public function getObjects(\Katu\Tools\Classes\ClassName $className)
	{
		$this->setIteratorArray($className);

		return $this->iteratorArray;
	}

	public function getColumnValues($column)
	{
		$values = [];

		foreach ($this as $row) {
			if (is_object($row)) {
				$values[] = $row->$column;
			} else {
				$values[] = $row[$column];
			}
		}

		return $values;
	}

	public function getIds()
	{
		return $this->getColumnValues('id');
	}

	public function each($callback)
	{
		$res = [];
		foreach ($this as $item) {
			if (is_string($callback) && method_exists($item, $callback)) {
				$res[] = call_user_func_array([$item, $callback], [$item]);
			} else {
				$res[] = call_user_func_array($callback, [$item]);
			}
		}

		return $res;
	}

	/****************************************************************************
	 * Iterator.
	 */
	public function rewind()
	{
		$this->iteratorPosition = 0;
	}

	public function current()
	{
		$this->setIteratorArray();

		return $this->iteratorArray[$this->iteratorPosition];
	}

	public function key()
	{
		return $this->iteratorPosition;
	}

	public function next()
	{
		++$this->iteratorPosition;
	}

	public function valid()
	{
		$this->setIteratorArray();

		return isset($this->iteratorArray[$this->iteratorPosition]);
	}

	/****************************************************************************
	 * ArrayAccess.
	 */
	public function setIteratorArray(\Katu\Tools\Classes\ClassName $className = null)
	{
		if (is_null($this->iteratorArray)) {
			$this->iteratorArray = [];
			if ($className) {
				while ($row = $this->statement->fetchObject((string)$className)) {
					$this->iteratorArray[] = $row;
				}
			} else {
				while ($row = $this->statement->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
					$this->iteratorArray[] = $row;
				}
			}
		}
	}

	public function offsetSet($offset, $value)
	{
		$this->setIteratorArray();

		if (is_null($offset)) {
			$this->iteratorArray[] = $value;
		} else {
			$this->iteratorArray[$offset] = $value;
		}
	}

	public function offsetExists($offset)
	{
		$this->setIteratorArray();

		return isset($this->iteratorArray[$offset]);
	}

	public function offsetUnset($offset)
	{
		$this->setIteratorArray();

		unset($this->iteratorArray[$offset]);
	}

	public function offsetGet($offset)
	{
		$this->setIteratorArray();

		return isset($this->iteratorArray[$offset]) ? $this->iteratorArray[$offset] : null;
	}
}
