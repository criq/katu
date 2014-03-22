<?php

namespace Jabli;

class Session {

	const DEFAULT_LIFETIME = 86400;
	const DEFAULT_PATH     = '/';
	const DEFAULT_SECURE   = FALSE;
	const DEFAULT_HTTPONLY = FALSE;

	static function setCookieParams() {
		try {
			$config = \Jabli\Config::get('session');
		} catch (Exception $e) {
			$config = array();
		}

		$config = array_merge(self::getDefaultConfig(), $config);

		return session_set_cookie_params($config['lifetime'], $config['path'], $config['domain'], $config['secure'], $config['httponly']);
	}

	static function getDefaultConfig() {
		return array(
			'lifetime' => self::DEFAULT_LIFETIME,
			'path'     => self::DEFAULT_PATH,
			'domain'   => self::getDefautCookieDomain(),
			'secure'   => self::DEFAULT_SECURE,
			'httponly' => self::DEFAULT_HTTPONLY,
		);
	}

	static function getDefautCookieDomain() {
		return '.' . implode('.', array_slice(explode('.', $_SERVER['HTTP_HOST']), -2));
	}

	static function get($name = NULL) {
		if (!session_id()) {
			session_start();
			self::setCookieParams();
		}

		if (!$name) {
			return $_SESSION;
		}

		if (!isset($_SESSION[$name])) {
			return NULL;
		}

		return $_SESSION[$name];
	}

	static function set($name, $value) {
		if (!session_id()) {
			session_start();
			self::setCookieParams();
		}

		return $_SESSION[$name] = $value;
	}

}
