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

	public function setValue(?string $value): Header
	{
		$this->value = $value;

		return $this;
	}
}
