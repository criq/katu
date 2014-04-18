<?php

namespace Katu\Utils;

use \Katu\Session;

class CSRF {

	const TOKEN_TIMEOUT = 3600;
	const TOKEN_LENGTH  = 10;

	static function getFreshToken() {
		$token = array(Random::getString(self::TOKEN_LENGTH), time());

		$tokens = self::getValidTokens();
		$tokens[] = $token;

		Session::set('csrfTokens', $tokens);

		return $token[0];
	}

	static function getValidTokens() {
		return array_values(array_filter(self::getTokens(), function($i) {
			return Datetime::get($i[1])->isInTimeout(self::TOKEN_TIMEOUT);
		}));
	}

	static function getTokens() {
		return (array) Session::get('csrfTokens');
	}

	static function isValidToken($token) {
		return in_array($token, array_map(function($i) {
			return $i[0];
		}, self::getValidTokens()));
	}

}
