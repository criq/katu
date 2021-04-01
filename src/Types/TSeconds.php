<?php

namespace Katu\Types;

class TSeconds
{
	public $value;

	public function __construct(float $value)
	{
		$this->value = $value;
	}

	public function __toString() : string
	{
		return (string)$this->getValue();
	}

	public function getValue() : float
	{
		return (float)$this->value;
	}

	public function getDateTime() : \Katu\Tools\DateTime\DateTime
	{
		return new \Katu\Tools\DateTime\DateTime($this->getValue() . ' seconds');
	}
}
