<?php

namespace Katu\Tools\Cookies;

class Cookie {

	const DEFAULT_LIFETIME = 86400;
	const DEFAULT_PATH     = '/';
	const DEFAULT_SECURE   = false;
	const DEFAULT_HTTPONLY = true;

	static function set($name, $value = null, $lifetime = null, $path = null, $domain = null) {
		$config = self::getConfig();

		$name = strtr($name, '.', '_');
		$lifetime = !is_null($lifetime) ? (time() + (int) $lifetime) : (time() + $config['lifetime']);
		$path     = !is_null($path) ? $path : $config['path'];
		$domain   = !is_null($domain) ? $domain : $config['domain'];

		return setcookie($name, $value, $lifetime, $path, $domain);
	}

	static function get($name = null) {
		$name = strtr($name, '.', '_');

		if (!$name) {
			return $_COOKIE;
		}

		return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
	}

	static function remove($name) {
		return self::set($name, null, -86400);
	}

	static function getDefaultConfig() {
		return [
			'lifetime' => self::DEFAULT_LIFETIME,
			'path'     => self::DEFAULT_PATH,
			'domain'   => self::getDefautDomain(),
			'secure'   => self::DEFAULT_SECURE,
			'httponly' => self::DEFAULT_HTTPONLY,
		];
	}

	static function getConfig() {
		try {
			$config = \Katu\Config::getApp('cookie');
		} catch (\Exception $e) {
			$config = [];
		}

		return array_merge(self::getDefaultConfig(), $config);
	}

	static function getDefautDomain() {
		return '.' . \Katu\Tools\Routing\URL::getBase()->get2ndLevelDomain();
	}

}
