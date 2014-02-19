<?php

namespace Jabli\Aids;

class Config {

	static function get() {
		if (!defined('BASE_DIR')) {
			throw new Exception("Undefined BASE_DIR.");
		}

		$filename = rtrim(BASE_DIR) . '/config.php';
		if (!is_readable($filename)) {
			throw new Exception("Missing config file at " . $filename . ".");
		}

		$config = include $filename;
		if (!is_array($config)) {
			throw new Exception("Invalid config array for env hash " . Env::getHash() . ".");
		}

		foreach (func_get_args() as $key) {
			if (isset($config[$key])) {
				$config = $config[$key];
			} else {
				throw new Exception("Invalid config key.");
			}
		}

		return $config;
	}

}
