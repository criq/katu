<?php

namespace Katu\Exceptions;

class ModelNotFoundException extends NotFoundException {

	public function __construct($message = 'Not found.', $code = 0, $previous = null) {
		parent::__construct($message, $code, $previous);
	}

}
