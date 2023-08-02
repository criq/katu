<?php

namespace Katu\Tools\Forms;

use Katu\Tools\Session\Session;

class TokenCollection extends \ArrayObject
{
	const KEY = "CSRF_TOKENS";

	public static function createFromSession(): TokenCollection
	{
		$session = new Session;
		if (!($session->getVariable(static::KEY) instanceof TokenCollection)) {
			$session->setVariable(static::KEY, new static);
		}

		return $session->getVariable(static::KEY);
	}

	public function persist(): TokenCollection
	{
		$session = new Session;
		$session->setVariable(static::KEY, $this->filterAcceptable());

		return $this;
	}

	public function filterFresh(): TokenCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Token $token) {
			return $token->isFresh();
		})));
	}

	public function filterAcceptable(): TokenCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Token $token) {
			return $token->isAcceptable();
		})));
	}

	public function filterByCode(string $code): TokenCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Token $token) use ($code) {
			return $token->getCode() == $code;
		})));
	}

	public function sortByTTL(): TokenCollection
	{
		$array = $this->getArrayCopy();
		usort($array, function (Token $a, Token $b) {
			return $a->getTTL() > $b->getTTL() ? -1 : 1;
		});

		return new static($array);
	}
}
