<?php

namespace Katu;

class Session {

	const REFERENCE_KEY = 'fw.session';

	static function init() {
		static::setCookieParams();

		if (!session_id()) {
			session_start();
		}

		if (!isset($_SESSION[static::REFERENCE_KEY])) {
			$_SESSION[static::REFERENCE_KEY] = array();
		}

		return TRUE;
	}

	static function &getReference() {
		self::init();

		return $_SESSION[static::REFERENCE_KEY];
	}

	static function get($key = NULL) {
		static::init();

		$reference =& static::getReference();

		if (!$key) {
			return $reference;
		}

		if (!isset($reference[$key])) {
			return NULL;
		}

		return $reference[$key];
	}

	static function set($key, $value) {
		static::init();

		$reference =& static::getReference();
		$reference[$key] = $value;

		return TRUE;
	}

	static function add($key, $value) {
		static::init();

		$reference =& static::getReference();
		$reference[$key][] = $value;

		return TRUE;
	}

	static function reset() {
		static::init();

		$reference =& static::getReference();
		$reference = NULL;

		return TRUE;
	}

	static function setCookieParams($config = array()) {
		try {
			$config = \Katu\Config::getApp('cookie');
		} catch (\Exception $e) {
			$config = array();
		}

		$config = array_merge(Cookie::getDefaultConfig(), $config);

		return session_set_cookie_params($config['lifetime'], $config['path'], $config['domain'], $config['secure'], $config['httponly']);
	}

}
