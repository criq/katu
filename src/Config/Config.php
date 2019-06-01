<?php

namespace Katu\Config;

class Config {

	static function get() {
		$args = func_get_args();

		return \Katu\Cache\Runtime::get(array_merge(['config'], $args), function() use($args) {
			try {
				return call_user_func_array([new \Katu\Types\TArray(static::getAll()), 'getValueByArgs'], $args);
			} catch (\Katu\Exceptions\MissingArrayKeyException $e) {
				throw new \Katu\Exceptions\MissingConfigException("Missing config for " . implode('.', $args) . ".");
			}
		});
	}

	static function getAll() {
		return \Katu\Cache\Runtime::get('config', function() {

			$config = [];
			foreach (static::getFiles() as $file) {
				$pathinfo = pathinfo($file);
				if (!isset($config[$pathinfo['filename']])) {
					$config[$pathinfo['filename']] = [];
				}
				if ($pathinfo['extension'] == 'yaml') {
					$config[$pathinfo['filename']] = array_merge($config[$pathinfo['filename']], (array)\Katu\Files\Formats\YAML::decode($file));
				} else {
					$config[$pathinfo['filename']] = array_merge($config[$pathinfo['filename']], (array)include $file);
				}
			}

			return $config;

		});
	}

	static function getFiles() {
		$dir = BASE_DIR . "/app/Config";
		$files = [];

		foreach (scandir($dir) as $file) {
			if (preg_match("/^[a-z]+\.(php|yaml)$/i", $file)) {
				$files[] = \Katu\Files\File::joinPaths($dir, $file);
			}
		}

		return $files;
	}

}
