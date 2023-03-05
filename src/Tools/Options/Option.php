<?php

namespace Katu\Tools\Options;

use Katu\Tools\Strings\Code;

class Option
{
	protected $code;
	protected $value;

	public function __construct(string $code, $value)
	{
		$this->setCode(new Code($code));
		$this->setValue($value);
	}

	public function __toString(): string
	{
		return $this->getCode()->getConstantFormat();
	}

	public function setCode(Code $code): Option
	{
		$this->code = $code;

		return $this;
	}

	public function getCode(): Code
	{
		return $this->code;
	}

	public function setValue($value): Option
	{
		$this->value = $value;

		return $this;
	}

	public function getValue()
	{
		return $this->value;
	}
}
