<?php

namespace Katu\Types;

class TEmailAddress
{
	public $value;

	public function __construct($value)
	{
		if (!self::isValid($value)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid e-mail address.");
		}

		$this->value = (string)trim($value);
	}

	public function __toString() : string
	{
		return (string)$this->value;
	}

	public static function isValid($value) : bool
	{
		return (bool)filter_var($value, \FILTER_VALIDATE_EMAIL);
	}

	public function getDomain() : string
	{
		return explode('@', $this->value)[1];
	}
}
