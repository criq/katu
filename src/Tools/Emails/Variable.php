<?php

namespace Katu\Tools\Emails;

class Variable
{
	protected $key;
	protected $value;

	public function __construct(string $key, ?string $value)
	{
		$this->setKey($key);
		$this->setValue($value);
	}

	public function setKey(string $key): Variable
	{
		$this->key = $key;

		return $this;
	}

	public function getKey(): string
	{
		return $this->key;
	}

	public function setValue(?string $value): Variable
	{
		$this->value = $value;

		return $this;
	}

	public function getValue(): ?string
	{
		return $this->value;
	}
}
