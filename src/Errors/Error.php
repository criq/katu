<?php

namespace Katu\Errors;

use Katu\Interfaces\Packaged;
use Katu\Types\TClass;
use Katu\Types\TPackage;

class Error implements Packaged
{
	protected $message;
	protected $names = [];
	protected $code;
	protected $versions = [];
	protected $comment;
	protected $payload = [];

	public function __construct(string $message = null, ?array $names = [], ?string $code = null, ?array $versions = [])
	{
		$this->setMessage($message);
		$this->setNames($names);
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

	public function setNames(?array $value)
	{
		$this->names = $value;

		return $this;
	}

	public function getNames(): array
	{
		return $this->names ?: [];
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

	public function setComment(?string $value): Error
	{
		$this->comment = $value;

		return $this;
	}

	public function getComment(): ?string
	{
		return $this->comment;
	}

	public function setPayload(?array $value): Error
	{
		$this->payload = $value;

		return $this;
	}

	public function getPayload(): array
	{
		return $this->payload ?: [];
	}

	public function getResponseArray(): array
	{
		return [
			"message" => $this->getMessage(),
			"code" => $this->getCode(),
			"versions" => $this->getVersions(),
			"names" => $this->getNames(),
			"comment" => $this->getComment(),
			"payload" => $this->getPayload(),
		];
	}
}
