<?php

namespace Katu;

class Cookie {

	const DEFAULT_LIFETIME = 86400;
	const DEFAULT_PATH     = '/';
	const DEFAULT_SECURE   = FALSE;
	const DEFAULT_HTTPONLY = FALSE;

	static function set($name, $value = NULL, $lifetime = NULL) {
		$config = self::getConfig();

		$name = strtr($name, '.', '_');
		$lifetime = !is_null($lifetime) ? (time() + (int) $lifetime) : (time() + $config['lifetime']);

		return setcookie($name, $value, $lifetime, $config['path'], $config['domain']);
	}

	static function get($name) {
		$name = strtr($name, '.', '_');

		return isset($_COOKIE[$name]) ? $_COOKIE[$name] : NULL;
	}

	static function remove($name) {
		return self::set($name, NULL, -86400);
	}



	static function getDefaultConfig() {
		return array(
			'lifetime' => self::DEFAULT_LIFETIME,
			'path'     => self::DEFAULT_PATH,
			'domain'   => self::getDefautDomain(),
			'secure'   => self::DEFAULT_SECURE,
			'httponly' => self::DEFAULT_HTTPONLY,
		);
	}

	static function getConfig() {
		try {
			$config = \Katu\Config::getApp('cookie');
		} catch (\Exception $e) {
			$config = array();
		}

		return array_merge(self::getDefaultConfig(), $config);
	}

	static function getDefautDomain() {
		return '.' . Utils\URL::getBase()->get2ndLevelDomain();
	}

}
