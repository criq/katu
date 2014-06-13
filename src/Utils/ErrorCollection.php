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
		if ($this->exists($name)) {
			throw new \Exception("Callback exists.");
		}

		if ($callable instanceof Callback) {
			$this->callbackCollection[$name] = $callable;
		} else {
			$this->callbackCollection[$name] = new Callback($callable);
		}
	}

}
