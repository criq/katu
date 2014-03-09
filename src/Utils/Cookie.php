<?php

namespace Jabli\Utils;

class Cookie {

	static function setCookie($name, $value = NULL, $timeout = 0) {
		return setcookie($name, $value, $timeout ? (time() + $timeout) : 0, '/');
	}

	static function unsetCookie($name) {
		return self::setCookie($name, NULL, -86400);
	}

}
