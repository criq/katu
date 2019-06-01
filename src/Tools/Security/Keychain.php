<?php

namespace Katu\Tools\Security;

class Keychain {

	static function get() {
		return \Katu\Tools\Cache\Runtime::get('keychain', function($args) {
			try {
				return call_user_func_array([new \Katu\Types\TArray(static::getAll()), 'getValueByArgs'], $args);
			} catch (\Katu\Exceptions\MissingArrayKeyException $e) {
				throw new \Katu\Exceptions\MissingConfigException("Missing keychain for " . implode('.', $args) . ".");
			}
		}, func_get_args());
	}

	static function getAll() {
		return \Katu\Tools\Cache\Runtime::get('keychain', function() {
			if (!defined('BASE_DIR')) {
				throw new \Exception("Undefined BASE_DIR.");
			}

			$path = BASE_DIR . '/app/Keychains/' . \Katu\Config\Env::getPlatform() . '.yaml';
			if (!file_exists($path)) {
				throw new \Exception("Missing keychain file.");
			}

			if (!is_readable($path)) {
				throw new \Exception("Unable to read keychain file.");
			}

			return \Katu\Tools\Files\Formats\YAML::decode($path);
		});
	}

}
