<?php

namespace Katu\Config;

use App\Config\EnvConfig;
use Katu\Types\TURL;

abstract class AppConfig extends \Katu\Config\Config
{
	public function getIsEnvironment(string $environment): bool
	{
		return mb_strtoupper($environment) === mb_strtoupper($this->getEnvironment());
	}

	public function getEnvironment(): string
	{
		return (new EnvConfig)->getVariable("APP_ENV");
	}

	public function getBaseURL(): TURL
	{
		$env = new EnvConfig;

		return new TURL("{$env->getVariable("APP_SCHEMA")}://{$env->getVariable("APP_HOST")}/");
	}

	public function getAPIURL(): ?TURL
	{
		return $this->getBaseURL();
	}
}
