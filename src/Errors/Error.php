<?php

namespace Katu\Errors;

use Katu\Interfaces\Packaged;
use Katu\Types\TClass;
use Katu\Types\TPackage;

class Error implements Packaged
{
	protected $code;
	protected $help;
	protected $message;
	protected $paramCollection;
	protected $options;
	protected $versions;

	public function __construct(string $message = null, ?string $code = null, ?array $versions = [])
	{
		$this->setMessage($message);
		$this->setCode($code);
		$this->setVersions($versions);
	}

	public function __toString(): string
	{
		return (string)$this->getMessage();
	}

	public static function createFromPackage(TPackage $package): Error
	{
		$class = TClass::createFromPortableName($package->getPayload()['classPortableName']);
		$className = $class->getName();

		return new $className($package->getPayload()['message'], $package->getPayload()['code']);
	}

	public function getPackage(): TPackage
	{
		return new TPackage([
			'classPortableName' => (new TClass($this))->getPortableName(),
			'message' => $this->getMessage(),
			'code' => $this->getCode(),
		]);
	}

	public function setMessage(string $value): Error
	{
		$this->message = $value;

		return $this;
	}

	public function getMessage(): string
	{
		return $this->message;
	}

	public function getMessageWithoutPeriod(): ?string
	{
		return rtrim($this->getMessage(), '.');
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

	public function addVersions(string $locale, string $message): Error
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

	public function setParamCollection(\Katu\Tools\Validation\ParamCollection $paramCollection): Error
	{
		$this->paramCollection = $paramCollection;

		return $this;
	}

	public function getParamCollection(): \Katu\Tools\Validation\ParamCollection
	{
		if (!$this->paramCollection) {
			$this->paramCollection = new \Katu\Tools\Validation\ParamCollection;
		}

		return $this->paramCollection;
	}

	public function getResponseArray(): array
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
		if (count($this->getParamCollection())) {
			$array["params"] = $this->getParamCollection()->getResponseArray();
		}

		return $array;
	}
}
