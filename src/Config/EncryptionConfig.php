<?php

namespace Katu\Config;

class EncryptionConfig extends Config
{
	public function getKey(): string
	{
		return \App\App::getEnvConfig()->getVariable("ENCRYPTION_KEY");
	}

	public function getSalt(): string
	{
		return \App\App::getEnvConfig()->getVariable("ENCRYPTION_SALT");
	}
}
