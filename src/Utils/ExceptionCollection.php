<?php

namespace Katu\Utils;

class ExceptionCollection {

	public $exceptionCollection;

	public function __construct() {

	}

	public function add(\Exception $exception) {
		$this->exceptionCollection[] = $exception;
	}

	public function hasExceptions() {
		return (bool) (count($this->exceptionCollection));
	}

}
