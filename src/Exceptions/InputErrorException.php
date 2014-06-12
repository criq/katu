<?php

namespace Katu\Exceptions;

class InputErrorException extends ErrorException {

	public $input;

	public function __construct($message, $input = NULL, $code = 0, $data = array(), $previous = NULL) {
		parent::__construct($message, $code, $data, $previous);
		$this->input = $input;
	}

}
