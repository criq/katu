<?php

namespace Katu\Utils;

class Callback {

	public $callable;

	public function __construct($callable = null) {
		if (!is_callable($callable)) {
			throw new \Exception("Not a callable.");
		}

		$this->callable = $callable;
	}

	public function call($args = []) {
		return call_user_func_array($this->callable, $args);
	}

}
