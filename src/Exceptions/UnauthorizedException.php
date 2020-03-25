<?php

namespace Katu\Exceptions;

class UnauthorizedException extends Exception
{
	public function __construct($message = 'Unauthorized.', $code = 0, $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
