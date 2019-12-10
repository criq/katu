<?php

namespace Katu\Tools\Security;

class Keychain {

	static function get() {
		return \Katu\Cache\Runtime::get('keychain', function($args) {
			try {
				return call_user_func_array([new \Katu\Types\TArray(static::getAll()), 'getValueByArgs'], $args);
			} catch (\Katu\Exceptions\MissingArrayKeyException $e) {
				throw new \Katu\Exceptions\MissingConfigException("Missing keychain for " . implode('.', $args) . ".");
			}
		}, func_get_args());
	}

	static function getAll() {
		return \Katu\Cache\Runtime::get('keychain', function() {
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
