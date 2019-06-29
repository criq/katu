<?php

namespace Katu\Tools\Security;

use \Katu\Tools\Session\Session;

class CSRF {

	static function getFreshToken($params = array()) {
		$token = new \Katu\Tools\Forms\Token($params);

		$tokens = static::getValidTokens();
		$tokens[] = $token;

		Session::set('csrf.tokens', $tokens);

		return $token;
	}

	static function getAllTokens() {
		return (array) Session::get('csrf.tokens');
	}

	static function getValidTokens() {
		return array_values(array_filter(static::getAllTokens(), function($token) {
			return $token->isValid();
		}));
	}

	static function getValidTokenByToken($tokenToken) {
		foreach (static::getValidTokens() as $token) {
			if ($token->token == $tokenToken && $token->isValid()) {
				return $token;
			}
		}

		return false;
	}

	static function isValidToken($tokenToken) {
		$token = static::getValidTokenByToken($tokenToken);
		if (!$token) {
			return false;
		}

		return $token->isValid();
	}

}
