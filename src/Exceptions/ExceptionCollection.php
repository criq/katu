<?php

namespace Katu\Exceptions;

class ExceptionCollection extends Exception implements \Iterator, \ArrayAccess {

	public $collection = [];

	protected $iteratorPosition = 0;

	public function __construct($message = null, $code = 0, $previous = null) {
		parent::__construct($message, $code, $previous);
	}

	public function setMessage($message = null) {
		$this->message = $message;
	}

	public function add() {
		return call_user_func_array(['static', 'addException'], func_get_args());
	}

	public function addException(\Exception $exception) {
		if ($exception instanceof ExceptionCollection) {
			foreach ($exception as $e) {
				$this->collection[] = $e;
			}
		} else {
			$this->collection[] = $exception;
		}

		return $this;
	}

	public function has() {
		return $this->hasExceptions();
	}

	public function hasExceptions() {
		return (bool) $this->countExceptions();
	}

	public function countExceptions() {
		return (int) (count($this->collection));
	}

	public function getErrorNames() {
		$errorNames = [];
		foreach ($this->collection as $exception) {
			$errorNames = array_merge($errorNames, $exception->getErrorNames());
		}

		return array_values(array_filter(array_unique($errorNames)));
	}

	public function replaceErrorName($errorName, $replacement) {
		foreach ($this->collection as $exception) {
			$exception->replaceErrorName($errorName, $replacement);
		}

		return $this;
	}

	public function getResponseArray() {
		return [
			'errors' => array_map(function($e) {
				return $e->getResponseArray();
			}, $this->collection),
		];
	}

	/* Iterator *****************************************************************/

	public function rewind() {
		$this->iteratorPosition = 0;
	}

	public function current() {
		return $this->collection[$this->iteratorPosition];
	}

	public function key() {
		return $this->iteratorPosition;
	}

	public function next() {
		++$this->iteratorPosition;
	}

	public function valid() {
		return isset($this->collection[$this->iteratorPosition]);
	}

	/* ArrayAccess **************************************************************/

	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->collection[] = $value;
		} else {
			$this->collection[$offset] = $value;
		}
	}

	public function offsetExists($offset) {
		return isset($this->collection[$offset]);
	}

	public function offsetUnset($offset) {
		unset($this->collection[$offset]);
	}

	public function offsetGet($offset) {
		return isset($this->collection[$offset]) ? $this->collection[$offset] : null;
	}

}
