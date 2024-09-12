<?php

namespace Katu\Tools\Emails;

use Katu\Errors\ErrorCollection;

class Response
{
	protected $errors;
	protected $exception;
	protected $messageId;
	protected $payload;
	protected $request;
	protected $status;

	public function __construct(Request $request)
	{
		$this->setRequest($request);
	}

	public function setRequest(Request $request): Response
	{
		$this->request = $request;

		return $this;
	}

	public function getRequest(): Request
	{
		return $this->request;
	}

	public function setStatus(?bool $status): Response
	{
		$this->status = $status;

		return $this;
	}

	public function getStatus(): ?bool
	{
		return $this->status;
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

	public function setMessageId(?string $messageId): Response
	{
		$this->messageId = $messageId;

		return $this;
	}

	public function getMessageId(): ?string
	{
		return $this->messageId;
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

	public function setException(?\Throwable $exception)
	{
		$this->exception = $exception;

		return $this;
	}

	public function getException(): ?\Throwable
	{
		return $this->exception;
	}

	public function hasException(): bool
	{
		return (bool)$this->getException();
	}
}
