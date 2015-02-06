<?php

namespace Katu;

use \Katu\Utils\Cache;

class Config {

	static function get() {
		$configName = implode('.', func_get_args());
		$cacheName  = 'config.' . $configName;

		$cached = Cache::getRuntime($cacheName);

		if (is_null($cached)) {

			$config = new \Katu\Types\TArray(self::getAll());

			try {

				$cached = call_user_func_array(array($config, 'getValueByArgs'), func_get_args());

			} catch (\Katu\Exceptions\MissingArrayKeyException $e) {

				throw new \Katu\Exceptions\MissingConfigException("Missing config for " . $configName . ".");

			}

		}

		return $cached;
	}

	static function getAll() {
		$cached = Cache::getRuntime('config');

		if (is_null($cached)) {

			$config = array();

			foreach (self::getFiles() as $file) {
				$pathinfo = pathinfo($file);
				if ($pathinfo['extension'] == 'yaml') {
					$config[$pathinfo['filename']] = \Katu\Utils\YAML::decode($file);
				} else {
					$config[$pathinfo['filename']] = include $file;
				}
			}

			$cached = Cache::setRuntime('config', $config);

		}

		return $cached;
	}

	static function getFiles() {
		$dir = BASE_DIR . '/app/Config';
		$files = array();

		foreach (scandir($dir) as $file) {
			if (preg_match('#^[a-z]+\.(php|yaml)$#i', $file)) {
				$files[] = Utils\FileSystem::joinPaths($dir, $file);
			}
		}

		return $files;
	}

	static function getApp() {
		return call_user_func_array(array('self', 'get'), array_merge(array('app'), func_get_args()));
	}

	static function getDB() {
		return call_user_func_array(array('self', 'get'), array_merge(array('db'), func_get_args()));
	}

}
