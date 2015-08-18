<?php

namespace Katu\Exceptions;

class ExceptionCollection extends Exception implements \Iterator {

	public $collection = [];

	private $iteratorPosition = 0;

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

	/* Iterator **************************************************************/

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

}
