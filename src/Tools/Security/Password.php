<?php

namespace Katu\Utils;

class Password
{
	const DELIMITER = '$';

	public static function getHashable($password, $salt)
	{
		return $password . $salt;
	}

	public static function encode($hash, $password)
	{
		$salt = \Katu\Tools\Random\Generator::getString();

		return static::DELIMITER . implode(static::DELIMITER, array($hash, $salt, hash($hash, static::getHashable($password, $salt))));
	}

	public static function verify($attempt, $token)
	{
		$analyzed = static::analyzeHashed($token);
		if (!$analyzed) {
			return false;
		}

		return hash($analyzed['hash'], static::getHashable($attempt, $analyzed['salt'])) == $analyzed['hashed'];
	}

	public static function analyzeHashed($token)
	{
		if (!$token) {
			return false;
		}

		$delimiter = substr($token, 0, 1);
		if (!$delimiter) {
			return false;
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
