<?php

namespace Jabli\Aids;

class JSON {

	static function encode($var) {
		return json_encode($var,
			defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0
			| defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : 0
			| defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0
		);
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
