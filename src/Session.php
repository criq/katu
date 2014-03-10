<?php

namespace Jabli;

class Session {

	static function get($name) {
		if (!session_id()) {
			session_start();
		}

		if (!isset($_SESSION[$name])) {
			return NULL;
		}

		return $_SESSION[$name];
	}

	static function set($name, $value) {
		if (!session_id()) {
			session_start();
		}

		return $_SESSION[$name] = $value;
	}

}
