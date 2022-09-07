<?php

namespace Katu\Tools\Emails;

use Katu\Types\TEmailAddress;

abstract class ThirdParty extends \Katu\Tools\Emails\Email
{
	protected $globalVariables = [];
	protected $recipientVariables = [];
	protected $template;

	abstract public function send();

	public function setTemplate(?string $template): ThirdParty
	{
		$this->template = $template;

		return $this;
	}

	public function getTemplate(): ?string
	{
		return (string)$this->template;
	}

	public function setGlobalVariable(string $name, string $value): ThirdParty
	{
		if (trim($name)) {
			$this->globalVariables[$name] = $value;
		}

		return $this;
	}

	public function setRecipientVariable(TEmailAddress $emailAddress, string $name, string $value): ThirdParty
	{
		if (trim($name)) {
			$this->recipientVariables[$emailAddress->getEmailAddress()][$name] = $value;
		}

		return $this;
	}
}
