<?php

namespace Katu\Exceptions;

class ArgumentErrorException extends ErrorException {

	public $argument;

	public function __construct($message, $argument = NULL, $code = 0, $data = array(), $previous = NULL) {
		parent::__construct($message, $code, $data, $previous);
		$this->argument = $argument;
	}

}
