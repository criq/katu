<?php

namespace Katu;

use \Katu\Utils\Cache;

class Keychain {

	static function get() {
		$cacheName = 'keychain.' . implode('.', func_get_args());
		$cached = Cache::getRuntime($cacheName);

		if (is_null($cached)) {

			$array = new \Katu\Types\TArray(self::getAll());

			$cached = Cache::setRuntime($cacheName, call_user_func_array(array($array, 'getValueByArgs'), func_get_args()));

		}

		return $cached;
	}

	static function getAll() {
		$cached = Cache::getRuntime('keychain');

		if (is_null($cached)) {

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

			$cached = Cache::setRuntime('keychain', Utils\YAML::decode($path));

		}

		return $cached;
	}

}
