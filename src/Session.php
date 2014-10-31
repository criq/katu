<?php

namespace Katu;

class Session {

	const REFERENCE_KEY = 'katu.session';

	static function start() {
		if (!session_id()) {
			session_start();
		}
	}

	static function init() {
		if (!session_id()) {
			static::setCookieParams();
			session_start();
		}

		if (!isset($_SESSION[static::REFERENCE_KEY])) {
			$_SESSION[static::REFERENCE_KEY] = array();
		}

		return true;
	}

	static function &getReference() {
		static::init();

		return $_SESSION[static::REFERENCE_KEY];
	}

	static function get($key = null) {
		static::init();

		$reference =& static::getReference();

		if (!$key) {
			return $reference;
		}

		if (!isset($reference[$key])) {
			return null;
		}

		return $reference[$key];
	}

	static function set($key, $value) {
		static::init();

		$reference =& static::getReference();
		$reference[$key] = $value;

		return true;
	}

	static function add($key, $value) {
		static::init();

		$reference =& static::getReference();
		if (trim($value)) {
			$reference[$key][] = $value;
		}

		return true;
	}

	static function reset($key = null) {
		static::init();

		if (!$key) {
			$reference =& static::getReference();
			$reference = null;

			return true;
		}

		static::set($key, null);

		return true;
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
