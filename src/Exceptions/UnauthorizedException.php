<?php

namespace Katu\Exceptions;

class UnauthorizedException extends Exception {

	public function __construct($message = '', $code = 0, $context = array(), $previous = NULL) {
		parent::__construct($message, $code, $context, $previous);
	}

}
