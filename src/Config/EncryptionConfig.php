<?php

namespace Katu\Config;

abstract class EncryptionConfig extends Config
{
	public function getKey(): string
	{
		return (new \App\Config\EnvConfig)->getVariable("ENCRYPTION_KEY");
	}

	public function getSalt(): string
	{
		return (new \App\Config\EnvConfig)->getVariable("ENCRYPTION_SALT");
	}
}
