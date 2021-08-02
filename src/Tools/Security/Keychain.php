<?php

namespace Katu\Tools\Security;

use Katu\Types\TIdentifier;

class Keychain
{
	public static function get()
	{
		return \Katu\Cache\Runtime::get(new TIdentifier('keychain'), function ($args) {
			try {
				return call_user_func_array([new \Katu\Types\TArray(static::getAll()), 'getValueByArgs'], $args);
			} catch (\Katu\Exceptions\MissingArrayKeyException $e) {
				throw new \Katu\Exceptions\MissingConfigException("Missing keychain for " . implode('.', $args) . ".");
			}
		}, func_get_args());
	}

	public static function getAll()
	{
		return \Katu\Cache\Runtime::get(new TIdentifier('keychain'), function () {
			$file = new \Katu\Files\File(\Katu\App::getBaseDir(), 'app', 'Keychains', [\Katu\Config\Env::getPlatform(), 'yaml']);
			if (!$file->exists()) {
				throw new \Exception("Missing keychain file.");
			}
			if (!$file->isReadable()) {
				throw new \Exception("Unable to read keychain file.");
			}

			return \Katu\Files\Formats\YAML::decode($file);
		});
	}
}
