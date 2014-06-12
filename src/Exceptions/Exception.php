<?php

namespace Katu\Exceptions;

class Exception extends \Exception {

	public $data;

	public function __construct($message, $code = 0, $data = array(), $previous = NULL) {
		parent::__construct($message, $code, $previous);
		$this->data = $data;
	}

}
