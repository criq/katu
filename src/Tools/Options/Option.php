<?php

namespace Katu\Tools\Options;

class Option
{
	protected $name;
	protected $value;

	public function __construct(string $name, $value)
	{
		$this->name = $name;
		$this->value = $value;
	}

	public function __toString(): string
	{
		return $this->getName();
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getValue()
	{
		return $this->value;
	}
}
