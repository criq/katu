<?php

namespace Katu\Tools\Validation;

class Param
{
	protected $key;
	protected $value;
	protected $originalValue;

	public function __construct(string $key, $value = null, $originalValue = null)
	{
		$this->setKey($key);
		$this->setValue($value);
		$this->setOriginalValue($originalValue);
	}

	public function setKey(string $value): Param
	{
		$this->key = $value;

		return $this;
	}

	public function getKey(): string
	{
		return $this->key;
	}

	public function setValue($value): Param
	{
		$this->value = $value;

		return $this;
	}

	public function getValue()
	{
		return $this->value;
	}

	public function setOriginalValue($value): Param
	{
		$this->originalValue = $value;

		return $this;
	}

	public function getOriginalValue()
	{
		return $this->originalValue;
	}
}
