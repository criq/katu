<?php

namespace Katu\Utils;

class Random {

	static function getFileName($length = 32) {
		$factory = new \RandomLib\Factory;
		$generator = $factory->getGenerator(new \SecurityLib\Strength(\SecurityLib\Strength::LOW));

		return $generator->generateString($length, 'abcdefghijklmnopqrstuvwxyz');
	}

	static function getString($length = 32) {
		$factory = new \RandomLib\Factory;
		$generator = $factory->getGenerator(new \SecurityLib\Strength(\SecurityLib\Strength::LOW));

		return $generator->generateString($length, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
	}

	static function getWord($length = 8, $seed = NULL) {
		$factory = new \RandomLib\Factory;
		$generator = $factory->getGenerator(new \SecurityLib\Strength(\SecurityLib\Strength::LOW));

		$seed = is_null($seed) ? rand(0, 1) : $seed;
		$consonants = ;
		$vowels = 'aeiouy';
		$word = '';

		for ($i = $seed; $i < $length + $seed; $i++) {
			if ($i % 2) {
				$word .= $generator->generateString(1, 'bcdfghjklmnpqrstvwxz');
			} else {
				$word .= $generator->generateString(1, $vowels);
			}
		}

		return $word;
	}

	static function getIDString($length = 32) {
		$factory = new \RandomLib\Factory;
		$generator = $factory->getGenerator(new \SecurityLib\Strength(\SecurityLib\Strength::LOW));

		return $generator->generateString($length, 'ABCDEFGHJKLMNPQRSTUVWXYZ123456789');
	}

}
