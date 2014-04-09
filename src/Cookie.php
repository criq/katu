<?php

namespace Jabli;

class Cookie {

	static function set($name, $value = NULL, $timeout = 0) {
		return setcookie(strtr($name, '.', '_'), $value, $timeout ? (time() + $timeout) : 0, '/', Types\URL::get2ndLevelDomain(Config::getApp('base_url')));
	}

	static function get($name) {
		$name = strtr($name, '.', '_');

		return isset($_COOKIE[$name]) ? $_COOKIE[$name] : NULL;
	}

	static function remove($name) {
		return self::set($name, NULL, -86400);
	}

}
