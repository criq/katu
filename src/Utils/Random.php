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

	static function getIDString($length = 32) {
		$factory = new \RandomLib\Factory;
		$generator = $factory->getGenerator(new \SecurityLib\Strength(\SecurityLib\Strength::LOW));

		return $generator->generateString($length, 'ABCDEFGHJKLMNPQRSTUVWXYZ123456789');
	}

}
