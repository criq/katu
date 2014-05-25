<?php

namespace Katu\Utils;

class Callbacks {

	public $callbacks;

	public function __construct($name = NULL, $callable = NULL) {
		if ($name || $callable) {
			$this->add($name, $callable);
		}
	}

	public function add($name, $callable) {
		if (!$name) {
			throw new \Exception("Missing callback name.");
		}
		if ($this->exists($name)) {
			throw new \Exception("Callback exists.");
		}
		if (!is_callable($callable)) {
			throw new \Exception("Callback is not callable.");
		}

		$this->callbacks[$name] = $callable;
	}

	public function exists($name) {
		return isset($this->callbacks[$name]);
	}

	public function call($name, $args = array()) {
		if (!$this->exists($name)) {
			throw new \Exception("Callback doesn't exist.");
		}

		return call_user_func_array($this->callbacks[$name], $args);
	}

}
