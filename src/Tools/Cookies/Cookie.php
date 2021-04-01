<?php

namespace Katu\Tools\Cookies;

class Cookie
{
	const DEFAULT_HTTPONLY = true;
	const DEFAULT_LIFETIME = '1 year';
	const DEFAULT_PATH = '/';
	const DEFAULT_SECURE = false;

	public static function set($name, $value = null, $lifetime = null, $path = null, $domain = null)
	{
		$config = static::getConfig();

		$name = strtr($name, '.', '_');
		$lifetime = !is_null($lifetime) ? (time() + (int) $lifetime) : (time() + $config['lifetime']);
		$path = !is_null($path) ? $path : $config['path'];
		$domain = !is_null($domain) ? $domain : $config['domain'];

		return setcookie($name, $value, $lifetime, $path, $domain);
	}

	public static function get($name = null)
	{
		$name = strtr($name, '.', '_');

		if (!$name) {
			return $_COOKIE;
		}

		return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
	}

	public static function remove($name)
	{
		return static::set($name, null, -86400);
	}

	public static function getDefaultConfig()
	{
		return [
			'lifetime' => abs((new \Katu\Tools\DateTime\DateTime('+ ' . static::DEFAULT_LIFETIME))->getAge()->getValue()),
			'path' => static::DEFAULT_PATH,
			'domain' => static::getDefautDomain(),
			'secure' => static::DEFAULT_SECURE,
			'httponly' => static::DEFAULT_HTTPONLY,
		];
	}

	public static function getConfig()
	{
		try {
			$config = \Katu\Config\Config::get('app', 'cookie');
		} catch (\Exception $e) {
			$config = [];
		}

		return array_merge(static::getDefaultConfig(), $config);
	}

	public static function getDefautDomain()
	{
		return '.' . \Katu\Tools\Routing\URL::getBase()->get2ndLevelDomain();
	}
}
