<?php

namespace Katu\Utils;

class Random {

	const ALPHA_LOWER = 'abcdefghijklmnopqrstuvwxyz';
	const ALPHA_UPPER = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	const ALPHA = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

	const ALNUM_UPPER = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	const ALNUM_LOWER = 'abcdefghijklmnopqrstuvwxyz0123456789';
	const ALNUM = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	const ALNUM_SPECIAL = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789$-_.+!*()';

	const NUM = '0123456789';

	const IDSTRING = 'ABCDEFGHJKLMNPQRSTUVWXYZ123456789';

	static function getFromChars($chars, $length = 32) {
		$factory = new \RandomLib\Factory;
		$generator = $factory->getGenerator(new \SecurityLib\Strength(\SecurityLib\Strength::LOW));

		return $generator->generateString($length, $chars);
	}

	static function getFileName($length = 32) {
		return static::getFromChars(static::ALPHA_SMALL, $length);
	}

	static function getString($length = 32) {
		return static::getFromChars(static::ALNUM, $length);
	}

	static function getIdString($length = 32) {
		return static::getFromChars(static::IDSTRING, $length);
	}

	static function getNumber($length = 32) {
		return static::getFromChars(static::NUM, $length);
	}

	static function getWord($length = 8, $seed = null) {
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
