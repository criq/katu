<?php

namespace Katu\Utils;

class Password
{
	const DELIMITER = "$";

	static function getHashable($password, $salt): string
	{
		return $password . $salt;
	}

	static function encode($hashFunction, $password): string
	{
		$salt = Random::getString();

		return static::DELIMITER . implode(static::DELIMITER, array($hashFunction, $salt, hash($hashFunction, static::getHashable($password, $salt))));
	}

	static function verify($attempt, $token): bool
	{
		$analyzed = static::analyzeHashed($token);
		if (!$analyzed) {
			return false;
		}

		return hash($analyzed["hash"], static::getHashable($attempt, $analyzed["salt"])) == $analyzed["hashed"];
	}

	static function analyzeHashed($token): array
	{
		if (!$token) {
			return false;
		}

		$delimiter = substr($token, 0, 1);
		if (!$delimiter) {
			return false;
		}

		list($hash, $salt, $hashed) = explode($delimiter, substr($token, 1));

		return [
			"delimiter" => $delimiter,
			"hash"      => $hash,
			"salt"      => $salt,
			"hashed"    => $hashed,
		];
	}
}
