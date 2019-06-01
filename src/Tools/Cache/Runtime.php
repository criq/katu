<?php

namespace Katu\Tools\Cache;

class Runtime {

	static $runtime = [];

	static function get($name, $callback = null) {
		$args = array_slice(func_get_args(), 0, 2);
		$key = new \Katu\Tools\Strings\Key([$name, $args]);

		// There's something cached.
		if (isset(static::$runtime[$key]) && !is_null(static::$runtime[$key])) {
			return static::$runtime[$key];
		}

		// There is callback.
		if (!is_null($callback)) {
			static::$runtime[$key] = call_user_func_array($callback, $args);
			return static::$runtime[$key];
		}

		return null;
	}

}
