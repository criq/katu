<?php

namespace Katu\Tools\HTML;

class Attribute implements StringableInterface
{
	protected $name;
	protected $value;

	public function __construct(string $name, ?string $value = null)
	{
		$this->name = $name;
		$this->value = $value;
	}

	public function __toString(): string
	{
		return "{$this->getName()}=\"{$this->getValue()}\"";
	}

	public function setName(string $name): Attribute
	{
		$this->name = $name;

		return $this;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setValue(?string $value = null): Attribute
	{
		$this->value = $value;

		return $this;
	}

	public function getValue(): ?string
	{
		return $this->value;
	}
}
