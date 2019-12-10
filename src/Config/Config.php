<?php

namespace Katu\Config;

class Config {

	const FILENAME_REGEXP = "/^(?<name>[a-z0-9]+)(\.(?<platform>[a-z0-9]+))?\.(?<type>php|yaml)$/i";

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
				if (preg_match(static::FILENAME_REGEXP, $file->getBasename(), $match)) {
					if (!$match['platform'] || $match['platform'] == Env::getPlatform()) {
						if ($match['type'] == 'yaml') {
							$config[$match['name']] = array_merge_recursive($config[$match['name']] ?? [], (array)\Katu\Files\Formats\YAML::decode($file));
						} elseif ($match['type'] == 'php') {
							$config[$match['name']] = array_merge_recursive($config[$match['name']] ?? [], (array)include $file);
						}
					}
				}
			}

			$config = array_merge_recursive($config, $_SERVER['CONFIG'] ?? []);
			#var_dump($config);die;

			return $config;

		});
	}

	static function getFiles() {
		$dir = new \Katu\Files\File(\Katu\App::getBaseDir(), 'app', 'Config');
		$files = [];

		foreach (scandir($dir) as $file) {
			if (preg_match(static::FILENAME_REGEXP, $file)) {
				$files[] = new \Katu\Files\File($dir, $file);
			}
		}

		return $files;
	}

}
