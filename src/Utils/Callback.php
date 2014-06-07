<?php

namespace Katu\Utils;

class Callback {

	public $callable;

	public function __construct($callable = NULL) {
		if (!is_callable($callable)) {
			throw new \Exception("Not a callable.");
		}

		$this->callable = $callable;
	}

	public function call($args = array()) {
		return call_user_func_array($this->callable, $args);
	}

}
