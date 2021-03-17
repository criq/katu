<?php

namespace Katu\Exceptions;

class ForbiddenException extends Exception
{
	const HTTP_CODE = 403;

	public function __construct($message = 'Forbidden.', $code = 0, $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
