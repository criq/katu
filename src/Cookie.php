<?php

namespace Jabli;

class Cookie {

	static function setCookie($name, $value = NULL, $timeout = 0) {
		return setcookie($name, $value, $timeout ? (time() + $timeout) : 0, '/', Utils\URL::get2ndLevelDomain(Config::getApp('base_url')));
	}

	static function getCookie($name) {
		return isset($_COOKIE[$name]) ? $_COOKIE[$name] : NULL;
	}

	static function unsetCookie($name) {
		return self::setCookie($name, NULL, -86400);
	}

}
