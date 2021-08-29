<?php

namespace Katu;

class Session
{
	const KEY = 'katu.session';

	public static function getPath()
	{
		return new Utils\File(TMP_PATH, 'session');
	}

	public static function makePath()
	{
		try {
			return static::getPath()->makeDir();
		} catch (\Exception $e) {
			// Nevermind.
		}
	}

	public static function start()
	{
		if (!session_id()) {
			static::makePath();
			session_save_path(static::getPath());
			session_start();
		}
	}

	public static function init()
	{
		if (!session_id()) {
			static::setCookieParams();
			static::start();
		}

		if (!isset($_SESSION[static::KEY])) {
			$_SESSION[static::KEY] = [];
		}

		return true;
	}

	public static function get($key = null)
	{
		static::init();

		if (!$key) {
			return $_SESSION[static::KEY];
		}

		if (!isset($_SESSION[static::KEY][$key])) {
			return null;
		}

		return $_SESSION[static::KEY][$key];
	}

	public static function set()
	{
		static::init();

		$_SESSION[static::KEY][func_get_arg(0)] = func_get_arg(1);

		return true;
	}

	public static function add($key, $value, $instance = null)
	{
		static::init();

		if ($value) {
			if (!is_null($instance)) {
				$_SESSION[static::KEY][$key][$instance] = $value;
			} else {
				$_SESSION[static::KEY][$key][] = $value;
			}
		}

		return true;
	}

	public static function reset()
	{
		static::init();

		if (func_get_args()) {
			foreach (func_get_args() as $key) {
				static::set($key, null);
			}
		} else {
			$_SESSION[static::KEY] = null;
		}

		return true;
	}

	public static function setCookieParams($config = [])
	{
		try {
			$config = \Katu\Config::getApp('cookie');
		} catch (\Exception $e) {
			$config = [];
		}

		$config = array_merge(Cookie::getDefaultConfig(), $config);

		return @session_set_cookie_params($config['lifetime'], $config['path'], $config['domain'], $config['secure'], $config['httponly']);
	}
}
