<?php

namespace Katu\Tools\Cookies;

use Psr\Http\Message\ServerRequestInterface;

class CookieCollection extends \ArrayObject
{
	public function offsetSet($key, $value)
	{
		parent::offsetSet($value->getKey(), $value);
	}

	public static function createFromRequest(ServerRequestInterface $request)
	{
		$res = new static;
		foreach ((array)$request->getCookieParams() as $key => $value) {
			$res[] = new Cookie($key, $value);
		}

		return $res;
	}

	public function getCookie(string $key): ?Cookie
	{
		return $this[$key] ?? null;
	}

	public function getCookieValue(string $key): ?string
	{
		$cookie = $this->getCookie($key);

		return $cookie ? $cookie->getValue() : null;
	}
}
