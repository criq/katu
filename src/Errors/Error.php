<?php

namespace Katu\Errors;

use Katu\Tools\Package\Package;
use Katu\Tools\Package\PackagedInterface;
use Katu\Tools\Rest\RestResponse;
use Katu\Tools\Rest\RestResponseInterface;
use Katu\Types\TClass;
use Psr\Http\Message\ServerRequestInterface;

class Error implements PackagedInterface, RestResponseInterface
{
	protected $code;
	protected $help;
	protected $message;
	protected $params;
	protected $options;
	protected $versions;

	public function __construct(?string $message = null, ?string $code = null, ?array $versions = [])
	{
		$this->setMessage($message);
		$this->setCode($code);
		$this->setVersions($versions);
	}

	public function __toString(): string
	{
		return (string)$this->getMessage();
	}

	public static function createFromPackage(Package $package): Error
	{
		$class = TClass::createFromPortableName($package->getPayload()["class"]);
		$className = $class->getName();

		return new $className($package->getPayload()["message"], $package->getPayload()["code"]);
	}

	public function getPackage(): Package
	{
		return new Package([
			"class" => (new TClass($this))->getPortableName(),
			"message" => $this->getMessage(),
			"code" => $this->getCode(),
		]);
	}

	public function setMessage(?string $value): Error
	{
		$this->message = $value;

		return $this;
	}

	public function getMessage(): ?string
	{
		return $this->message;
	}

	public function getMessageWithoutPeriod(): ?string
	{
		return rtrim($this->getMessage(), ".");
	}

	public function setCode(?string $value): Error
	{
		$this->code = $value;

		return $this;
	}

	public function getCode(): ?string
	{
		return $this->code;
	}

	public function setVersions(?array $value): Error
	{
		$this->versions = $value;

		return $this;
	}

	public function addVersion(string $locale, string $message): Error
	{
		$this->versions[$locale] = $message;

		return $this;
	}

	public function getVersions(): array
	{
		return $this->versions ?: [];
	}

	public function setHelp(?string $value): Error
	{
		$this->help = $value;

		return $this;
	}

	public function getHelp(): ?string
	{
		return $this->help;
	}

	public function setOptions(?array $value): Error
	{
		$this->options = $value;

		return $this;
	}

	public function getOptions(): ?array
	{
		return $this->options;
	}

	public function setParamCollection(\Katu\Tools\Validation\ParamCollection $params): Error
	{
		$this->params = $params;

		return $this;
	}

	public function getParams(): \Katu\Tools\Validation\ParamCollection
	{
		if (!$this->params) {
			$this->params = new \Katu\Tools\Validation\ParamCollection;
		}

		return $this->params;
	}

	public function addParam(\Katu\Tools\Validation\Param $param): Error
	{
		$this->getParams()->append($param);

		return $this;
	}

	public function getRestResponse(?ServerRequestInterface $request = null): RestResponse
	{
		$array = [
			"message" => $this->getMessage(),
		];

		if ($this->getCode()) {
			$array["code"] = $this->getCode();
		}
		if ($this->getVersions()) {
			$array["versions"] = $this->getVersions();
		}
		if ($this->getHelp()) {
			$array["help"] = $this->getHelp();
		}
		if ($this->getOptions()) {
			$array["options"] = $this->getOptions();
		}
		if (count($this->getParams())) {
			$array["params"] = $this->getParams()->getRestResponse();
		}

		return new RestResponse($array);
	}
}
