<?php

namespace Katu\Tools\Session;

abstract class Flash
{
	protected $message;

	public function __construct(string $message)
	{
		$this->setMessage($message);
	}

	public function setMessage(string $message): Flash
	{
		$this->message = $message;

		return $this;
	}

	public function getMessage(): string
	{
		return $this->message;
	}
}
