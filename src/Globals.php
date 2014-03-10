<?php

namespace Jabli;

class Globals {

	static function get($name) {
		if (!isset($GLOBALS[$name])) {
			return NULL;
		}

		return $GLOBALS[$name];
	}

	static function set($name, $value) {
		return $GLOBALS[$name] = $value;
	}

}
