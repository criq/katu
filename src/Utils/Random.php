<?php

namespace Katu\Utils;

class Random {

	static function getFromChars($chars, $length = 32) {
		$factory = new \RandomLib\Factory;
		$generator = $factory->getGenerator(new \SecurityLib\Strength(\SecurityLib\Strength::LOW));

		return $generator->generateString($length, $chars);
	}

	static function getFileName($length = 32) {
		return static::getFromChars('abcdefghijklmnopqrstuvwxyz', $length);
	}

	static function getString($length = 32) {
		return static::getFromChars('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', $length);
	}

	static function getIDString($length = 32) {
		return static::getFromChars('ABCDEFGHJKLMNPQRSTUVWXYZ123456789', $length);
	}

	static function getNumber($length = 32) {
		return static::getFromChars('0123456789', $length);
	}

	static function getWord($length = 8, $seed = NULL) {
		$seed = is_null($seed) ? rand(0, 1) : $seed;
		$word = '';

		for ($i = $seed; $i < $length + $seed; $i++) {
			if ($i % 2) {
				$word .= static::getFromChars('bcdfghjklmnpqrstvwxz', 1);
			} else {
				$word .= static::getFromChars('aeiouy', 1);
			}
		}

		return $word;
	}

}
