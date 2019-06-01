<?php

namespace Katu\Tools\Session;

class Session {

	const KEY = 'katu.session';

	static function getPath() {
		return new Utils\File(TMP_PATH, 'session');
	}

	static function makePath() {
		try {
			return static::getPath()->makeDir();
		} catch (\Exception $e) {

		}
	}

	static function start() {
		if (!session_id()) {
			static::makePath();
			session_save_path(static::getPath());
			session_start();
		}
	}

	static function init() {
		if (!session_id()) {
			static::setCookieParams();
			static::start();
		}

		if (!isset($_SESSION[static::KEY])) {
			$_SESSION[static::KEY] = array();
		}

		return true;
	}

	static function get($key = null) {
		static::init();

		if (!$key) {
			return $_SESSION[static::KEY];
		}

		if (!isset($_SESSION[static::KEY][$key])) {
			return null;
		}

		return $_SESSION[static::KEY][$key];
	}

	static function set() {
		static::init();

		$_SESSION[static::KEY][func_get_arg(0)] = func_get_arg(1);

		return true;
	}

	static function add($key, $value, $instance = null) {
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

	static function reset() {
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

	static function setCookieParams($config = array()) {
		try {
			$config = \Katu\Config\Config::getApp('cookie');
		} catch (\Exception $e) {
			$config = array();
		}

		$config = array_merge(\Katu\Tools\Cookies\Cookie::getDefaultConfig(), $config);

		return session_set_cookie_params($config['lifetime'], $config['path'], $config['domain'], $config['secure'], $config['httponly']);
	}

}
