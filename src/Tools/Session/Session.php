<?php

namespace Katu\Tools\Session;

class Session {

	const KEY = 'katu.session';

	const DEFAULT_NAME                   = 'session';
	const DEFAULT_SID_LENGTH             = 32;
	const DEFAULT_SID_BITS_PER_CHARACTER = 6;
	const DEFAULT_COOKIE_LIFETIME        = '10 years';

	static function getPath() {
		return new \Katu\Files\File(TMP_PATH, 'session');
	}

	static function makePath() {
		try {
			return static::getPath()->makeDir();
		} catch (\Exception $e) {
			// Nevermind.
		}
	}

	static function getDefaultConfig() {
		return [
			'save_path'              => (string)static::getPath(),
			'name'                   => static::DEFAULT_NAME,
			//'sid_length'             => static::DEFAULT_SID_LENGTH,
			//'sid_bits_per_character' => static::DEFAULT_SID_BITS_PER_CHARACTER,
			'cookie_lifetime'        => abs((new \Katu\Tools\DateTime\DateTime('+ ' . static::DEFAULT_COOKIE_LIFETIME))->getAge()),
		];
	}

	static function getConfig() {
		try {
			$config = \Katu\Config\Config::get('app', 'session');
		} catch (\Exception $e) {
			$config = [];
		}

		return array_merge(static::getDefaultConfig(), $config);
	}

	static function start() {
		if (!session_id()) {
			static::makePath();
			session_start(static::getConfig());
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
			$config = \Katu\Config\Config::get('app', 'cookie');
		} catch (\Exception $e) {
			$config = array();
		}

		$config = array_merge(\Katu\Tools\Cookies\Cookie::getDefaultConfig(), $config);

		return session_set_cookie_params($config['lifetime'], $config['path'], $config['domain'], $config['secure'], $config['httponly']);
	}

}