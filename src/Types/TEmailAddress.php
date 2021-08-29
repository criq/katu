<?php

namespace Katu\Types;

class TEmailAddress
{
	public $value;

	public function __construct($value)
	{
		if (!static::isValid($value)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid e-mail address.");
		}

		$this->value = (string) (trim($value));
	}

	public function __toString()
	{
		return (string)$this->value;
	}

	public static function isValid($value)
	{
		return filter_var($value, FILTER_VALIDATE_EMAIL);
	}
}
