<?php

namespace Katu\Tools\Validation;

abstract class Rule implements ValidatableInterface
{
	protected $message;

	abstract public function validate(Param $param): Validation;

	public function __construct(?string $message = null)
	{
		$this->setMessage($message);
	}

	public function setMessage(string $message): Rule
	{
		$this->message = $message;

		return $this;
	}

	public function getMessage(): ?string
	{
		return $this->message;
	}
}
