<?php

namespace Jabli\Utils;

class JSON {

	static function getEncodeBitmask() {
		return
			  (defined('JSON_PRETTY_PRINT')      ? JSON_PRETTY_PRINT      : NULL)
			| (defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : NULL)
			| (defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : NULL)
		;
	}

	static function encode($var) {
		return json_encode($var, self::getEncodeBitmask());
	}

	static function encodeStandard($var) {
		return json_encode($var);
	}

	static function decodeAsObjects($var) {
		return @json_decode($var, FALSE) ?: FALSE;
	}

	static function decodeAsArray($var) {
		return @json_decode($var, TRUE) ?: FALSE;
	}

}
