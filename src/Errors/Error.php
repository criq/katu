<?php

namespace Katu\Errors;

use Katu\Interfaces\Packaged;
use Katu\Types\TClass;
use Katu\Types\TPackage;

class Error implements Packaged
{
	protected $message;
	protected $code;

	public function __construct(?string $message = null, ?string $code = null)
	{
		$this->message = $message;
		$this->code = $code;
	}

	public static function createFromPackage(TPackage $package): Error
	{
		return new static($package->getPayload()['message'], $package->getPayload()['code']);
	}

	public function getPackage(): TPackage
	{
		return new TPackage([
			'classPortableName' => (new TClass($this))->getPortableName(),
			'message' => $this->getMessage(),
			'code' => $this->getCode(),
		]);
	}

	public function getMessage(): ?string
	{
		return $this->string;
	}

	public function getCode(): ?string
	{
		return $this->code;
	}
}
