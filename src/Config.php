<?php

namespace Jabli;

class Config {

	static function get() {
		if (!defined('BASE_DIR')) {
			throw new Exception("Undefined BASE_DIR.");
		}

		// Try files at different locations.
		$locations = array(
			rtrim(BASE_DIR) . '/config.php',
			rtrim(BASE_DIR) . '/config/init.php',
			rtrim(BASE_DIR) . '/app/Config/init.php',
		);

		foreach ($locations as $location) {
			if (file_exists($location)) {
				$filename = $location;
				break;
			}
		}

		if (!isset($filename)) {
			throw new Exception("No config file found.");
		}

		if (!is_readable($filename)) {
			throw new Exception("Unable to read config file at " . $filename . ".");
		}

		$config = include $filename;
		if (!is_array($config)) {
			throw new Exception("Invalid config array for env hash " . Env::getHash() . ".");
		}

		foreach (func_get_args() as $key) {
			if (isset($config[$key])) {
				$config = $config[$key];
			} else {
				throw new Exception("Invalid config key " . $key . ".");
			}
		}

		return $config;
	}

	static function getSpec() {
		$specific = BASE_DIR . '/config/' . func_get_arg(0) . '.php';
		if (is_readable($specific)) {
			$config = include $specific;

			foreach (array_slice(func_get_args(), 1) as $key) {
				if (isset($config[$key])) {
					$config = $config[$key];
				} else {
					throw new Exception("Invalid config key " . $key . ".");
				}
			}

			return $config;
		}
	}

}
