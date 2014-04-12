<?php

namespace Katu;

class Flash {

	static function init() {
		if (!session_id()) {
			session_start();
		}

		if (!isset($_SESSION['fw.flash'])) {
			$_SESSION['fw.flash'] = array();
		}
	}

	static function get($key, $destroy = TRUE) {
		self::init();

		$value = isset($_SESSION['fw.flash'][$key]) ? $_SESSION['fw.flash'][$key] : NULL;
		if ($destroy && isset($_SESSION['fw.flash'][$key])) {
			unset($_SESSION['fw.flash'][$key]);
		}

		return $value;
	}

	static function set($key, $value) {
		self::init();

		return $_SESSION['fw.flash'][$key] = $value;
	}

	static function add($key, $value) {
		self::init();

		return $_SESSION['fw.flash'][$key][] = $value;
	}

	static function exists($key) {
		self::init();

		return isset($_SESSION['fw.flash'][$key]) && !is_null($_SESSION['fw.flash'][$key]);
	}

	static function reset() {
		self::init();

		return $_SESSION['fw.flash'] = NULL;
	}

}