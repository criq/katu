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
		return (string)$this->value;
	}
}
