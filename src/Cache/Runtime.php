<?php

namespace Katu\Cache;

class Runtime {

	static $cache = [];

	static function get($name, $callback = null) {
		$args = array_slice(func_get_args(), 2);
		$key = new \Katu\Tools\Keys\Hash([$name, $args]);

		// There's something cached.
		if (isset(static::$cache[(string)$key]) && !is_null(static::$cache[(string)$key])) {
			return static::$cache[(string)$key];
		}

		// There is callback.
		if (!is_null($callback)) {
			static::$cache[(string)$key] = call_user_func_array($callback, $args);
			return static::$cache[(string)$key];
		}

		return null;
	}

	static function clear() {
		return static::$cache = [];
	}

}
