<?php

namespace Katu\Config;

use App\Config\EnvConfig;

abstract class RedisConfig extends \Katu\Config\Config
{
	public function getHost(): string
	{
		return (new EnvConfig)->getVariable("REDIS_HOST") ?: "127.0.0.1";
	}

	public function getPort(): int
	{
		return (new EnvConfig)->getVariable("REDIS_PORT") ?: 6379;
	}
}
