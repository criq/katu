<?php

namespace Katu\Config;

use Katu\Types\TURL;

abstract class AppConfig extends \Katu\Config\Config
{
	abstract public function getEnvironment(): string;
	abstract public function getBaseURL(): TURL;
	abstract public function getCookieDomain(): string;

	public function getIsEnvironment(string $environment): bool
	{
		return mb_strtoupper($environment) === mb_strtoupper($this->getEnvironment());
	}
}
