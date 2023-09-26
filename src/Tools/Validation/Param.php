<?php

namespace Katu\Tools\Validation;

use Katu\Tools\Options\OptionCollection;
use Katu\Tools\Package\Package;
use Katu\Tools\Package\PackagedInterface;
use Katu\Tools\Rest\RestResponse;
use Katu\Tools\Rest\RestResponseInterface;
use Katu\Types\TClass;
use Psr\Http\Message\ServerRequestInterface;

class Param implements PackagedInterface, RestResponseInterface
{
	protected $aliases = [];
	protected $display;
	protected $input;
	protected $key;
	protected $output;

	public function __construct(?string $key, $input = null)
	{
		$this->setKey($key);
		$this->setInput($input);
	}

	public function __toString(): string
	{
		return (string)$this->getInput();
	}

	public function getPackage(): Package
	{
		return new Package([
			"class" => (new TClass($this))->getPackage(),
			"key" => (string)$this->getKey(),
			"aliases" => (array)$this->getAliases(),
			"input" => (string)$this->getInput(),
			"output" => (string)$this->getOutput(),
			"display" => (string)$this->getDisplay(),
		]);
	}

	public static function createFromPackage(Package $package)
	{
	}

	public function setKey(?string $value): Param
	{
		$this->key = $value;

		return $this;
	}

	public function getKey(): ?string
	{
		return $this->key;
	}

	public function addAlias(?string $alias): Param
	{
		$this->aliases[] = $alias;

		return $this;
	}

	public function getAliases(): array
	{
		return $this->aliases;
	}

	public function hasAlias(string $alias): bool
	{
		return in_array($alias, $this->getAliases());
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

	public function isNullInput(): bool
	{
		return is_null($this->getInput());
	}

	public function isNotNullInput(): bool
	{
		return !$this->isNullInput();
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

	public function setDisplay(string $value): Param
	{
		$this->display = $value;

		return $this;
	}

	public function getDisplay(): ?string
	{
		return $this->display;
	}

	public function forwardInput(): Param
	{
		$this->setOutput($this->getInput());

		return $this;
	}

	public function each(callable $callback): Param
	{
		$this->setOutput(call_user_func_array($callback, [$this->getOutput()]));

		return $this;
	}

	public function getRestResponse(?ServerRequestInterface $request = null, ?OptionCollection $options = null): RestResponse
	{
		return new RestResponse([
			"key" => $this->getKey(),
			"aliases" => $this->getAliases(),
			"input" => $this->getInput(),
		]);
	}
}
