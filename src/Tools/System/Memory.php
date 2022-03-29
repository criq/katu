<?php

namespace Katu\Tools\System;

use Katu\Types\TFileSize;

class Memory
{
	const TRESHOLD_CRITICAL = .9;

	public static function setLimit(TFileSize $limit): bool
	{
		return (bool)ini_set("memory_limit", $limit->getInB());
	}

	public static function getLimit(): TFileSize
	{
		return \Katu\Types\TFileSize::createFromShorthand(ini_get("memory_limit"));
	}

	public static function getUsage(): TFileSize
	{
		return new TFileSize((int)memory_get_usage());
	}

	public static function getUsedRatio(): float
	{
		return 1 - (static::getFree()->getInB()->getAmount() / static::getLimit()->getInB()->getAmount());
	}

	public static function getFree(): TFileSize
	{
		return new TFileSize(static::getLimit()->getInB()->getAmount() - static::getUsage()->getInB()->getAmount());
	}

	public static function isCritical(): bool
	{
		return static::getUsedRatio() >= static::TRESHOLD_CRITICAL;
	}
}
