<?php

namespace Katu\Tools\Strings;

class Replacement
{
	protected $code;
	protected $value;

	public function __construct(Code $code, ?string $value)
	{
		$this->setCode($code);
		$this->setValue($value);
	}

	public function setCode(Code $code): Replacement
	{
		$this->code = $code;

		return $this;
	}

	public function getCode(): Code
	{
		return $this->code;
	}

	public function setValue(?string $value): Replacement
	{
		$this->value = $value;

		return $this;
	}

	public function getValue(): ?string
	{
		return $this->value;
	}
}
