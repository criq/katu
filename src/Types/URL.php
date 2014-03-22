<?php

namespace Jabli\Types;

class URL {

	public $value;

	public function __construct($value) {
		if (!self::isValid($value)) {
			throw new Exception("Invalid URL.");
		}

		$this->value = (string) (trim($value));
	}

	static function isValid($value) {
		return filter_var(trim($value), FILTER_VALIDATE_URL) !== FALSE;
	}

}
