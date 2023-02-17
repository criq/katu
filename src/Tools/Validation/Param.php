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
	protected $key;
	protected $alias;
	protected $input;
	protected $output;
	protected $display;

	public function __construct(?string $key, $input = null, ?string $alias = null)
	{
		$this->setKey($key);
		$this->setInput($input);
		$this->setAlias($alias);
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
			"alias" => (string)$this->getAlias(),
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

	public function setAlias(?string $value): Param
	{
		$this->alias = $value;

		return $this;
	}

	public function getAlias(): ?string
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
		$this->setOutput($this->getInput() ?: null);

		return $this;
	}

	public function map(callable $callback): Param
	{
		$this->setOutput(call_user_func_array($callback, [$this->getOutput()]));

		return $this;
	}

	public function getRestResponse(?ServerRequestInterface $request = null, ?OptionCollection $options = null): RestResponse
	{
		return new RestResponse([
			"key" => $this->getKey(),
			"alias" => $this->getAlias(),
			"input" => $this->getInput(),
		]);
	}
}
