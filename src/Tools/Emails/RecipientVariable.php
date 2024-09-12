<?php

namespace Katu\Tools\Emails;

use Katu\Types\TEmailAddress;

class RecipientVariable
{
	protected $recipient;
	protected $variable;

	public function __construct(TEmailAddress $recipient, Variable $variable)
	{
		$this->setRecipient($recipient);
		$this->setVariable($variable);
	}

	public function setRecipient(TEmailAddress $recipient): RecipientVariable
	{
		$this->recipient = $recipient;

		return $this;
	}

	public function getRecipient(): TEmailAddress
	{
		return $this->recipient;
	}

	public function setVariable(Variable $variable): RecipientVariable
	{
		$this->variable = $variable;

		return $this;
	}

	public function getVariable(): Variable
	{
		return $this->variable;
	}
}
