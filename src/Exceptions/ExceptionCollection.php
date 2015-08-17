<?php

namespace Katu\Exceptions;

class ExceptionCollection extends Exception implements \Iterator {

	public $exceptionCollection = array();

	private $position = 0;

	public function __construct($message = null, $code = 0, $context = [], $previous = null) {
		parent::__construct($message, $code, $context, $previous);
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
				$this->exceptionCollection[] = $e;
			}
		} else {
			$this->exceptionCollection[] = $exception;
		}
	}

	public function has() {
		return $this->hasExceptions();
	}

	public function hasExceptions() {
		return (bool) $this->countExceptions();
	}

	public function countExceptions() {
		return (int) (count($this->exceptionCollection));
	}

	public function rewind() {
		$this->position = 0;
	}

	public function current() {
		return $this->exceptionCollection[$this->position];
	}

	public function key() {
		return $this->position;
	}

	public function next() {
		++$this->position;
	}

	public function valid() {
		return isset($this->exceptionCollection[$this->position]);
	}

}
