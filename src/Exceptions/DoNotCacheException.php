<?php

namespace Katu\Exceptions;

class DoNotCacheException extends Exception {

	public $data;

	public function __construct($data = null, $message = null, $code = 0, $context = [], $previous = null) {
		parent::__construct($message, $code, $context, $previous);

		$this->data = $data;
	}

}
