<?php

namespace Katu\Tools\HTML;

class Attribute implements HTMLInterface
{
	protected $name;
	protected $value;

	public function __construct(string $name, ?string $value = null)
	{
		$this->setName($name);
		$this->setValue($value);
	}

	public function __toString(): string
	{
		return (string)$this->getHTML();
	}

	public function getHTML(): HTML
	{
		return new HTML("{$this->getName()}=\"{$this->getValue()}\"");
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
