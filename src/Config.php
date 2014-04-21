<?php

namespace Katu;

use \Katu\Utils\Cache;

class Config {

	static function get() {
		$cacheName = 'config.' . implode('.', func_get_args());
		$cached = Cache::getRuntime($cacheName);

		if (is_null($cached)) {

			$config = new \Katu\Types\TArray(self::getAll());

			$cached = call_user_func_array(array($config, 'getValueByArgs'), func_get_args());

		}

		return $cached;
	}

	static function getAll() {
		$cached = Cache::getRuntime('config');

		if (is_null($cached)) {

			$config = array();

			foreach (self::getFiles() as $file) {
				$pathinfo = pathinfo($file);
				$config[$pathinfo['filename']] = include $file;
			}

			$cached = Cache::setRuntime('config', $config);

		}

		return $cached;
	}

	static function getFiles() {
		$dir = BASE_DIR . '/app/Config';
		$files = array();

		foreach (scandir($dir) as $file) {
			if (preg_match('#^[a-z]+\.php$#', $file)) {
				$files[] = Utils\FS::joinPaths($dir, $file);
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
