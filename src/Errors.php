<?php

namespace Katu;

class Errors implements \Iterator, \ArrayAccess {

	public $errors = [];

	protected $iteratorPosition = 0;

	public function addError($error) {
		if ($error instanceof \Katu\Exceptions\ExceptionCollection) {
			foreach ($error as $exception) {
				$this->addError($exception);
			}
		} else {
			$this->errors[] = $error;
		}

		return true;
	}

	public function getArray() {
		$array = [];

		foreach ($this->errors as $error) {
			if ($error instanceof \Katu\Exceptions\Exception) {
				$array = array_merge($array, $error->getArray());
			} elseif ($error instanceof \Exception) {
				$array[] = $error->getMessage();
			} else {
				$array[] = $error;
			}
		}

		return $array;
	}

	public function getNamed($errorName) {
		$name = \Katu\Exceptions\Exception::getErrorName($errorName);
		$array = $this->getArray();

		if (isset($array[$name])) {
			return $array[$name];
		}

		return false;
	}

	/* Iterator **************************************************************/

	public function rewind() {
		$this->iteratorPosition = 0;
	}

	public function current() {
		return $this->errors[$this->iteratorPosition];
	}

	public function key() {
		return $this->iteratorPosition;
	}

	public function next() {
		++$this->iteratorPosition;
	}

	public function valid() {
		return isset($this->errors[$this->iteratorPosition]);
	}

	/* ArrayAccess ***********************************************************/

	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->errors[] = $value;
		} else {
			$this->errors[$offset] = $value;
		}
	}

	public function offsetExists($offset) {
		return isset($this->errors[$offset]);
	}

	public function offsetUnset($offset) {
		unset($this->errors[$offset]);
	}

	public function offsetGet($offset) {
		return isset($this->errors[$offset]) ? $this->errors[$offset] : null;
	}

}
