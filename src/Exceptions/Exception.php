<?php

namespace Katu\Exceptions;

class Exception extends \Exception {

	public $context = array();

	public function __construct($message, $code = 0, $context = array(), $previous = NULL) {
		parent::__construct($message, $code, $previous);
		$this->context = $context;
	}

}
