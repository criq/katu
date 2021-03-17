<?php

namespace Katu\Errors;

class Collection implements \Iterator, \ArrayAccess
{
	protected $iteratorPosition = 0;
	public $errors = [];

	public function __construct($error = null)
	{
		if ($error) {
			$this->errors[] = $error;
		}
	}

	public function addError($error)
	{
		if ($error instanceof \Katu\Exceptions\Exceptions) {
			foreach ($error as $exception) {
				$this->addError($exception);
			}
		} else {
			$this->errors[] = $error;
		}

		return true;
	}

	public function getNamedArray()
	{
		$array = [];

		foreach ($this->errors as $error) {
			// Katu Exception.
			if ($error instanceof \Katu\Exceptions\Exception) {
				// Has error names.
				if ($error->getErrorNames()) {
					foreach ($error->getErrorNames() as $errorName) {
						if (!isset($array[$errorName])) {
							$array[$errorName] = new static;
						}
						$array[$errorName]->addError($error);
					}

				// Has no error names.
				} else {
					$array[] = $error;
				}
			} elseif ($error instanceof \Exception) {
				$array[] = $error->getMessage();
			} else {
				$array[] = $error;
			}
		}

		return $array;
	}

	public function getNamed($errorName)
	{
		$name = \Katu\Exceptions\Exception::getErrorName($errorName);
		$array = $this->getNamedArray();

		if (isset($array[$name])) {
			return $array[$name];
		}

		return false;
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
		return $this->errors[$this->iteratorPosition];
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
		return isset($this->errors[$this->iteratorPosition]);
	}

	/****************************************************************************
	 * ArrayAccess.
	 */
	public function offsetSet($offset, $value)
	{
		if (is_null($offset)) {
			$this->errors[] = $value;
		} else {
			$this->errors[$offset] = $value;
		}
	}

	public function offsetExists($offset)
	{
		$array = $this->getNamedArray();

		return isset($array[$offset]);
	}

	public function offsetUnset($offset)
	{
		unset($this->errors[$offset]);
	}

	public function offsetGet($offset)
	{
		$array = $this->getNamedArray();

		return isset($array[$offset]) ? $array[$offset] : null;
	}
}
