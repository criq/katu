<?php

namespace Katu\Utils;

class Memory
{
	const TRESHOLD_CRITICAL = .9;

	public static function getLimit()
	{
		if (preg_match('#^([0-9]+)M$#', ini_get('memory_limit'), $match)) {
			return 1024 * 1024 * (int) $match[1];
		}

		return 0;
	}

	public static function getUsage()
	{
		return (int) memory_get_usage();
	}

	public static function getUsedRatio()
	{
		return 1 - (self::getFree() / self::getLimit());
	}

	public static function getFree()
	{
		return self::getLimit() - self::getUsage();
	}

	public static function isCritical()
	{
		return self::getUsedRatio() >= self::TRESHOLD_CRITICAL;
	}
}
