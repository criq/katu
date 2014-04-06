<?php

namespace Jabli\Types;

use \Jabli\Exception;

class FWArray {

	public $value;

	public function __construct($value) {
		if (!self::isValid($value)) {
			throw new Exception("Invalid e-mail address.");
		}

		$this->value = $value;
	}

	static function isValid($value) {
		return is_array($value);
	}

	public function getValueByArgs() {
		$value = $this->value;

		foreach (func_get_args() as $key) {
			if (isset($value[$key])) {
				$value = $value[$key];
			} else {
				throw new Exception("Invalid key " . $key . ".");
			}
		}

		return $value;
	}

	public function getWithoutKeys() {
		$res = array();

		foreach ($this->value as $key => $value) {
			if (!in_array($key, func_get_args())) {
				$res[$key] = $value;
			}
		}

		return new self($res);
	}

}
