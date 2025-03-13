<?php

namespace Katu\Config;

use App\Config\EnvConfig;

abstract class CookieConfig extends \Katu\Config\Config
{
	public function getDomain(): string
	{
		return (new EnvConfig)->getVariable("APP_HOST");
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
