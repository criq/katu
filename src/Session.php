<?php

namespace Katu;

class Session {

	const REFERENCE_KEY = 'katu.session';

	static function init() {
		if (!session_id()) {
			static::setCookieParams();
			session_start();
		}

		if (!isset($_SESSION[static::REFERENCE_KEY])) {
			$_SESSION[static::REFERENCE_KEY] = array();
		}

		return TRUE;
	}

	static function &getReference() {
		static::init();

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
		if (trim($value)) {
			$reference[$key][] = $value;
		}

		return TRUE;
	}

	static function addError($error) {
		return static::add('errors', $error);
	}

	static function addSetError($set, $error) {
		return static::add('errors.' . $set, $error);
	}

	static function reset($key = NULL) {
		static::init();

		if (!$key) {
			$reference =& static::getReference();
			$reference = NULL;

			return TRUE;
		}

		static::set($key, NULL);

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
