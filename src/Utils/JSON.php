<?php

namespace Katu\Utils;

class JSON {

	static function respond($var) {
		$app = \Katu\App::get();

		$app->response->headers->set('Content-Type', 'application/json; charset=UTF-8');
		$app->response->setBody(self::encode($var));

		return true;
	}

	static function getEncodeBitmask() {
		return
			  (defined('JSON_PRETTY_PRINT')      ? JSON_PRETTY_PRINT      : null)
			| (defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : null)
			| (defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : null)
		;
	}

	static function encode($var) {
		return json_encode($var, self::getEncodeBitmask());
	}

	static function encodeStandard($var) {
		return json_encode($var);
	}

	static function decodeAsObjects($var) {
		return @json_decode($var, false);
	}

	static function decodeAsArray($var) {
		return @json_decode($var, true);
	}

}
