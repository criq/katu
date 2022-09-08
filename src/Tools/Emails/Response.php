<?php

namespace Katu\Tools\Emails;

use Katu\Errors\ErrorCollection;

class Response
{
	protected $errors;
	protected $payload;
	protected $status;

	public function __construct(bool $status)
	{
		$this->status = $status;
	}

	public function setPayload($payload): Response
	{
		$this->payload = $payload;

		return $this;
	}

	public function getPayload()
	{
		return $this->payload;
	}

	public function setErrors(?ErrorCollection $errors): Response
	{
		$this->errors = $errors;

		return $this;
	}

	public function getErrors(): ErrorCollection
	{
		return $this->errors ?: new ErrorCollection;
	}

	public function hasErrors(): bool
	{
		return $this->getErrors()->hasErrors();
	}
}
