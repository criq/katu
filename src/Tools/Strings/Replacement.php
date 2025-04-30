<?php

namespace Katu\Tools\Strings;

class Replacement
{
	protected $code;
	protected $description;
	protected $value;

	public function __construct(Code $code, ?string $value, ?string $description = null)
	{
		$this->setCode($code);
		$this->setValue($value);
		$this->setDescription($description);
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

	public function setDescription(?string $description): Replacement
	{
		$this->description = $description;

		return $this;
	}

	public function getDescription(): ?string
	{
		return $this->description;
	}
}
