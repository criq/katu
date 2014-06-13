<?php

namespace Katu\Exceptions;

class NamedErrorException extends ErrorException {

	public $name;

	public function __construct($message, $name = NULL, $code = 0, $data = array(), $previous = NULL) {
		parent::__construct($message, $code, $data, $previous);
		$this->name = $name;
	}

}
