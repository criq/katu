<?php

namespace Jabli\Aids;

class Config {

	static function get() {
		$config = include rtrim(BASE_DIR) . '/config.php';

		if (!is_array($config)) {
			throw new Exception("Invalid config array.");
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
