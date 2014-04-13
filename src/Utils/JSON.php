<?php

namespace Katu\Utils;

class JSON {

	static function respond($var) {
		$app = \Katu\App::get();

		$app->response->headers->set('Content-Type', 'application/json; charset=UTF-8');
		$app->response->setBody(self::encode($var));

		return TRUE;
	}

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
		return @json_decode($var, FALSE);
	}

	static function decodeAsArray($var) {
		return @json_decode($var, TRUE);
	}

}
