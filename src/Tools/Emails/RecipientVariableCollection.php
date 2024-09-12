<?php

namespace Katu\Tools\Emails;

use Katu\Types\TEmailAddress;

class RecipientVariableCollection extends \ArrayObject
{
	public function filterByRecipient(TEmailAddress $recipient): RecipientVariableCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (RecipientVariable $recipientVariable) use ($recipient) {
			return $recipientVariable->getRecipient()->getEmailAddress() == $recipient->getEmailAddress();
		})));
	}

	public function getVariables(): VariableCollection
	{
		return new VariableCollection(array_map(function (RecipientVariable $recipientVariable) {
			return $recipientVariable->getVariable();
		}, $this->getArrayCopy()));
	}
}
