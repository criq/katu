<?php

namespace Katu\Exceptions;

class UnauthorizedException extends Exception
{
	const HTTP_CODE = 401;

	public function __construct($message = 'Unauthorized.', $code = 0, $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
