<?php

namespace Katu\Utils;

class Password {

	const DELIMITER = '$';

	static function getHashable($password, $salt) {
		return $password . $salt;
	}

	static function encode($hash, $password) {
		$salt = Random::getString();

		return static::DELIMITER . implode(static::DELIMITER, array($hash, $salt, hash($hash, static::getHashable($password, $salt))));
	}

	static function verify($attempt, $token) {
		$analyzed = static::analyzeHashed($token);
		if (!$analyzed) {
			return FALSE;
		}

		return hash($analyzed['hash'], static::getHashable($attempt, $analyzed['salt'])) == $analyzed['hashed'];
	}

	static function analyzeHashed($token) {
		if (!$token) {
			return FALSE;
		}

		$delimiter = substr($token, 0, 1);
		if (!$delimiter) {
			return FALSE;
		}

		list($hash, $salt, $hashed) = explode($delimiter, substr($token, 1));

		return array(
			'delimiter' => $delimiter,
			'hash'      => $hash,
			'salt'      => $salt,
			'hashed'    => $hashed,
		);
	}

}
