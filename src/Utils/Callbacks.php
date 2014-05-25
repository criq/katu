<?php

namespace Katu\Utils;

class Callbacks {

	public $callbacks;

	public function __construct() {
		// One arg, an array of callbacks.
		if (count(func_get_args()) == 1 && is_array(func_get_arg(0))) {
			foreach (func_get_arg(0) as $name => $callable) {
				$this->add($name, $callable);
			}

		// Two args, name and callable.
		} elseif (count(func_get_args()) == 2) {
			$this->add(func_get_arg(0), func_get_arg(1));

		// Unable to process.
		} elseif (count(func_get_args())) {
			throw new \Exception("Invalid callback arguments.");
		}
	}

	public function add($name, $callable) {
		if (!$name) {
			throw new \Exception("Missing callback name.");
		}
		if ($this->exists($name)) {
			throw new \Exception("Callback exists.");
		}

		if ($callable instanceof Callback) {
			$this->callbacks[$name] = $callable;
		} else {
			$this->callbacks[$name] = new Callback($callable);
		}
	}

	public function exists($name) {
		return isset($this->callbacks[$name]);
	}

	public function call($name, $args = array()) {
		if (!$this->exists($name)) {
			throw new \Exception("Callback doesn't exist.");
		}

		return $this->callbacks[$name]->call($args);
	}

}
