<?php

namespace Katu\Tools\Options;

use Katu\Tools\Strings\Code;

class Option
{
	protected $code;
	protected $value;

	public function __construct(Code $code, $value)
	{
		$this->code = $code;
		$this->value = $value;
	}

	public function __toString(): string
	{
		return $this->getCode()->getConstantFormat();
	}

	public function getCode(): Code
	{
		return $this->code;
	}

	public function getValue()
	{
		return $this->value;
	}
}
