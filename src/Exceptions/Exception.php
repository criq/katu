<?php

namespace Katu\Exceptions;

class Exception extends \Exception {

	public $context = [];

	public function __construct($message = null, $code = 0, $context = [], $previous = null) {
		parent::__construct($message, $code, $previous);

		$this->context = $context;
	}

}
