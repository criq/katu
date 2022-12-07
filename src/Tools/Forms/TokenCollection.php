<?php

namespace Katu\Tools\Forms;

class TokenCollection extends \ArrayObject
{
	const SESSION_KEY = "csrfTokens";

	public static function getSessionTokenCollection(): TokenCollection
	{
		return \Katu\Tools\Session\Session::get(static::SESSION_KEY) ?: new TokenCollection;
	}

	public function saveToSession(): TokenCollection
	{
		$tokenCollection = $this->filterAcceptable();

		\Katu\Tools\Session\Session::set(static::SESSION_KEY, $tokenCollection);

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
