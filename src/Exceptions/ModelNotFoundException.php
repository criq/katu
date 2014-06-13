<?php

namespace Katu\Exceptions;

class ModelNotFoundException extends NotFoundException {

	public function __construct($message = '', $code = 0, $context = array(), $previous = NULL) {
		parent::__construct($message, $code, $context, $previous);
	}

}
