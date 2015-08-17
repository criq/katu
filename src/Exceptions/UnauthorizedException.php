<?php

namespace Katu\Exceptions;

class UnauthorizedException extends Exception {

	public function __construct($message = 'Unauthorized.', $code = 0, $context = [], $previous = null) {
		parent::__construct($message, $code, $context, $previous);
	}

}
