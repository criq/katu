<?php

namespace Katu\Tools\Security;

class CSRF
{
	public static function getFreshToken($params = [])
	{
		$token = new \Katu\Tools\Forms\Token($params);

		$tokens = static::getValidTokens();
		$tokens[] = $token;

		\Katu\Tools\Session\Session::set('csrf.tokens', $tokens);

		return $token;
	}

	public static function getAllTokens()
	{
		return (array) \Katu\Tools\Session\Session::get('csrf.tokens');
	}

	public static function getValidTokens()
	{
		return array_values(array_filter(static::getAllTokens(), function ($token) {
			return $token->isValid();
		}));
	}

	public static function getValidTokenByToken($tokenToken)
	{
		foreach (static::getValidTokens() as $token) {
			if ($token->token == $tokenToken && $token->isValid()) {
				return $token;
			}
		}

		return false;
	}

	public static function isValidToken($tokenToken)
	{
		$token = static::getValidTokenByToken($tokenToken);
		if (!$token) {
			return false;
		}

		return $token->isValid();
	}
}
