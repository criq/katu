<?php

namespace Katu\Exceptions;

class DoNotCacheException extends Exception {

	public $data;

	public function __construct($data = null, $message = null, $code = 0, $previous = null) {
		parent::__construct($message, $code, $previous);

		$this->data = $data;
	}

}
