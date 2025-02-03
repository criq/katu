<?php

namespace Katu\Tools\HTTPTools;

class Header
{
	protected $name;
	protected $value;

	public function __construct(string $name, ?string $value)
	{
		$this->setName($name);
		$this->setValue($value);
	}

	public function setName(string $name): Header
	{
		$this->name = $name;

		return $this;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public static function getComparableName(string $name): string
	{
		return mb_strtoupper(trim($name));
	}

	public function hasName(string $name): bool
	{
		return $this->getComparableName($name) == $this->getComparableName($this->getName());
	}

	public function setValue(?string $value): Header
	{
		$this->value = $value;

		return $this;
	}

	public function getValue(): ?string
	{
		return $this->value;
	}
}
