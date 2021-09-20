<?php

namespace Katu\Tools\Random;

class Generator
{
	const ALNUM = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	const ALNUM_LOWER = 'abcdefghijklmnopqrstuvwxyz0123456789';
	const ALNUM_SPECIAL = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789$-_.+!*()';
	const ALNUM_UPPER = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	const ALPHA = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	const ALPHA_LOWER = 'abcdefghijklmnopqrstuvwxyz';
	const ALPHA_UPPER = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	const IDSTRING = 'ABCDEFGHJKLMNPQRSTUVWXYZ123456789';
	const NUM = '0123456789';

	public static function getFromChars($chars, $length = 32)
	{
		try {
			$factory = new \RandomLib\Factory;
			$generator = $factory->getGenerator(new \SecurityLib\Strength(\SecurityLib\Strength::LOW));

			return $generator->generateString($length, $chars);
		} catch (\Throwable $e) {
			return static::generateString($length, $chars);
		}
	}

	public static function generateString($length, $chars)
	{
		$characters = [];
		foreach (range(1, $length) as $i) {
			$characters[] = $chars[rand(0, mb_strlen($chars) - 1)];
		}

		return implode($characters);
	}

	public static function getFileName($length = 32)
	{
		return static::getFromChars(static::ALPHA_LOWER, $length);
	}

	public static function getString($length = 32)
	{
		return static::getFromChars(static::ALNUM, $length);
	}

	public static function getIdString($length = 32)
	{
		return static::getFromChars(static::IDSTRING, $length);
	}

	public static function getNumber($length = 32)
	{
		return static::getFromChars(static::NUM, $length);
	}

	public static function getWord($length = 8, $seed = null)
	{
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
