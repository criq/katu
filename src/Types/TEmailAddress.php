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

		$this->value = (string) (trim($value));
	}

	public function __toString()
	{
		return (string) $this->value;
	}

	public static function isValid($value)
	{
		$validator = new \Egulias\EmailValidator\EmailValidator;
		return $validator->isValid($value, new \Egulias\EmailValidator\Validation\RFCValidation);
	}

	public function getDomain()
	{
		return explode('@', $this->value)[1];
	}
}
