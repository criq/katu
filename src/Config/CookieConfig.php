<?php

namespace Katu\Config;

class CookieConfig extends \Katu\Config\Config
{
	public function getDomain(): string
	{
		return \App\App::getEnvConfig()->getVariable("COOKIE_DOMAIN") ?: \App\App::getEnvConfig()->getVariable("APP_HOST");
	}

	public function getIsHTTPOnly(): bool
	{
		return true;
	}

	public function getIsSecure(): bool
	{
		return true;
	}

	public function getPath(): string
	{
		return "/";
	}

	public function getLifetime(): int
	{
		return 86400 * 365;
	}
}
