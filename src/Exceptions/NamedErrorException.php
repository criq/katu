<?php

namespace Katu\Exceptions;

class NamedErrorException extends ErrorException {

	public $name;

	public function __construct($message, $name = null, $code = 0, $previous = null) {
		parent::__construct($message, $code, $previous);

	}

	public function getName() {
		return $this->name;
	}

}
