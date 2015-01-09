<?php

namespace Katu\Exceptions;

class NamedErrorException extends ErrorException {

	public $name;

	public function __construct($message, $name = null, $code = 0, $context = array(), $previous = null) {
		parent::__construct($message, $code, $context, $previous);
		$this->name = $name;
	}

	public function getName() {
		return $this->name;
	}

}
