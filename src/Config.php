<?php

namespace Jabli;

class Config {

	static function get() {
		$filename = BASE_DIR . '/app/Config/' . func_get_arg(0) . '.php';
		if (!is_readable($filename)) {
			throw new Exception("Unable to read config file at " . $filename . ".");
		}

		$config = include $filename;

		$array = new \Jabli\Types\FWArray($config);

		return call_user_func_array(array($array, 'getValueByArgs'), array_slice(func_get_args(), 1));
	}

	static function getApp() {
		if (!defined('BASE_DIR')) {
			throw new Exception("Undefined BASE_DIR.");
		}

		// Try files at different locations.
		$locations = array(
			rtrim(BASE_DIR) . '/config.php',
			rtrim(BASE_DIR) . '/config/app.php',
			rtrim(BASE_DIR) . '/app/Config/app.php',
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

		$array = new \Jabli\Types\FWArray($config);

		return call_user_func_array(array($array, 'getValueByArgs'), func_get_args());
	}

}
