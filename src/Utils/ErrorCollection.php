<?php

namespace Katu\Utils;

class ErrorCollection {

	public $errorCollection;

	public function __construct() {

	}

	public function add($name, $callable) {
		if (!$name) {
			throw new \Exception("Missing error name.");
		}

		$this->errorCollection[$name] = new Callback($callable);
	}

}
