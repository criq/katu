<?php

namespace Katu\Tools\Validation;

class Param
{
	protected $key;
	protected $alias;
	protected $input;
	protected $output;

	public function __construct(string $key, $input = null, ?string $alias = null)
	{
		$this->setKey($key);
		$this->setInput($input);
		$this->setAlias($alias);
	}

	public function __toString(): string
	{
		return (string)$this->getInput();
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

	public function setInput($value): Param
	{
		$this->input = $value;

		return $this;
	}

	public function getInput()
	{
		return $this->input;
	}

	public function setOutput($value): Param
	{
		$this->output = $value;

		return $this;
	}

	public function getOutput()
	{
		return $this->output;
	}

	public function forward(): Param
	{
		$this->setOutput($this->getInput());

		return $this;
	}

	public function map(callable $callback): Param
	{
		$this->setOutput(call_user_func_array($callback, [$this->getOutput()]));

		return $this;
	}

	public function getResponseArray(): array
	{
		return [
			"key" => $this->getKey(),
			"alias" => $this->getAlias(),
			"input" => $this->getInput(),
		];
	}
}
