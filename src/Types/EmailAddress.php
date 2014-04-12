<?php

namespace Katu\Types;

class EmailAddress {

	public $value;

	public function __construct($value) {
		if (!self::isValid($value)) {
			throw new Exception("Invalid e-mail address.");
		}

		$this->value = (string) (trim($value));
	}

	static function isValid($value) {
		$validator = new \Egulias\EmailValidator\EmailValidator();

		return $validator->isValid($value);
	}

}
