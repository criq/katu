<?php

namespace Katu\Tools\Validation;

class Param
{
	protected $key;
	protected $alias;
	protected $value;
	protected $validatedValue;

	public function __construct(string $key, $value = null, ?string $alias = null)
	{
		$this->setKey($key);
		$this->setValue($value);
		$this->setAlias($alias);
	}

	public function __toString(): string
	{
		return (string)$this->getValue();
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

	public function setAlias(?string $value): Param
	{
		$this->alias = $value;

		return $this;
	}

	public function getAlias(): string
	{
		return $this->alias ?: $this->getKey();
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

	public function setValidatedValue($value): Param
	{
		$this->validatedValue = $value;

		return $this;
	}

	public function getValidatedValue()
	{
		return $this->validatedValue;
	}

	public function getResponseArray(): array
	{
		return [
			"key" => $this->getKey(),
			"alias" => $this->getAlias(),
			"value" => $this->getValue(),
		];
	}
}
