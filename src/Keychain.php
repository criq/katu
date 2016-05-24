<?php

namespace Katu;

use \Katu\Utils\Cache;

class Keychain {

	static function get() {
		$args = func_get_args();

		return Cache::getRuntime(array_merge(['keychain'], $args), function() use($args) {
			try {
				return call_user_func_array([new \Katu\Types\TArray(self::getAll()), 'getValueByArgs'], $args);
			} catch (\Katu\Exceptions\MissingArrayKeyException $e) {
				throw new \Katu\Exceptions\MissingConfigException("Missing keychain for " . implode('.', $args) . ".");
			}
		});
	}

	static function getAll() {
		return Cache::getRuntime('keychain', function() {
			if (!defined('BASE_DIR')) {
				throw new \Exception("Undefined BASE_DIR.");
			}

			$path = BASE_DIR . '/app/Keychains/' . Env::getPlatform() . '.yaml';
			if (!file_exists($path)) {
				throw new \Exception("Missing keychain file.");
			}

			if (!is_readable($path)) {
				throw new \Exception("Unable to read keychain file.");
			}

			return Utils\YAML::decode($path);
		});
	}

}
