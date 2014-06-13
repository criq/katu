<?php

namespace Katu\Exceptions;

class ExceptionCollection extends Exception implements \Iterator {

	public $exceptionCollection = array();

	private $position = 0;

	public function __construct($message = NULL, $code = 0, $context = array(), $previous = NULL) {
		parent::__construct($message, $code, $context, $previous);
	}

	public function setMessage($message = NULL) {
		$this->message = $message;
	}

	public function addException(\Exception $exception) {
		$this->exceptionCollection[] = $exception;
	}

	public function hasExceptions() {
		return (bool) (count($this->exceptionCollection));
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
