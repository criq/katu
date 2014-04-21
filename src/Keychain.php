<?php

namespace Katu;

class Keychain {

	static function get() {
		$array = new \Katu\Types\TArray(self::getAll());

		return call_user_func_array(array($array, 'getValueByArgs'), func_get_args());
	}

	static function getAll() {
		if (!isset($GLOBALS['app.keychain'])) {

			if (!defined('BASE_DIR')) {
				throw new Exception("Undefined BASE_DIR.");
			}

			$path = BASE_DIR . '/app/Keychains/' . Env::getPlatform() . '.yaml';
			if (!file_exists($path)) {
				throw new Exception("Missing keychain file.");
			}

			if (!is_readable($path)) {
				throw new Exception("Unable to read keychain file.");
			}

			$GLOBALS['app.keychain'] = \Katu\Utils\YAML::decode($path);

		}

		return $GLOBALS['app.keychain'];
	}

}
