<?php

namespace Katu\Exceptions;

class NoPrimaryKeyReturnedException extends \Katu\Exceptions\Exception
{
	public function setContext(?array $context): \Katu\Exceptions\Exception
	{
		return parent::setContext($context);
	}

	public function getContext(): ?array
	{
		return parent::getContext();
	}
}
