<?php

namespace Jabli\Utils;

class CSRF {

	const TOKEN_TIMEOUT = 3600;

	static function initialize() {

	}

	static function getFreshToken() {
		$token = array(Random::getString(), time());

		$tokens = self::getValidTokens();
		$tokens[] = $token;

		\Jabli\Session::set('fw.csrf_tokens', $tokens);

		return $token[0];
	}

	static function getValidTokens() {
		return array_values(array_filter(self::getTokens(), function($i) {
			return Datetime::get($i[1])->isInTimeout(self::TOKEN_TIMEOUT);
		}));
	}

	static function getTokens() {
		return (array) \Jabli\Session::get('fw.csrf_tokens');
	}

	static function isValidToken($token) {
		return in_array($token, array_map(function($i) {
			return $i[0];
		}, self::getValidTokens()));
	}

}
