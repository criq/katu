<?php

namespace Katu\Config;

class RedisConfig extends \Katu\Config\Config
{
	public function getHost(): string
	{
		return \App\App::getEnvConfig()->getVariable("REDIS_HOST") ?: "127.0.0.1";
	}

	public function getPort(): int
	{
		return \App\App::getEnvConfig()->getVariable("REDIS_PORT") ?: 6379;
	}
}
