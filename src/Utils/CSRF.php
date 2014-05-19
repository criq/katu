<?php

namespace Katu\Utils;

use \Katu\Session;
use \Katu\Utils\DateTime;

class CSRF {

	static function getFreshToken() {
		$token = new \Katu\Form\Token();

		$tokens = self::getValidTokens();
		$tokens[] = $token;

		Session::set('csrf.tokens', $tokens);

		return $token;
	}

	static function getAllTokens() {
		return (array) Session::get('csrf.tokens');
	}

	static function getValidTokens() {
		return array_values(array_filter(self::getAllTokens(), function($token) {
			return $token->isValid();
		}));
	}

	static function getValidTokenByToken($tokenToken) {
		foreach (static::getValidTokens() as $token) {
			if ($token->token == $tokenToken && $token->isValid()) {
				return $token;
			}
		}

		return FALSE;
	}

	static function isValidToken($tokenToken) {
		$token = static::getValidTokenByToken($tokenToken);
		if (!$token) {
			return FALSE;
		}

		return $token->isValid();
	}

}
