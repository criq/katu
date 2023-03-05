<?php

namespace Katu\Tools\Calendar;

class Seconds
{
	public $value;

	public function __construct(float $value)
	{
		$this->value = $value;
	}

	public function __toString(): string
	{
		return (string)$this->getValue();
	}

	public static function createFromString(string $string): Seconds
	{
		return new static((new Time($string))->getTimestamp() - (new Time())->getTimestamp());
	}

	public function getValue(): float
	{
		return (float)$this->value;
	}

	public function getDateTime(): Time
	{
		return new Time("{$this->getValue()} seconds");
	}

	public function getMinutes(): float
	{
		return $this->getValue() / 60;
	}
}
