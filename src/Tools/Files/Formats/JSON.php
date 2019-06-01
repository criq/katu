<?php

namespace Katu\Utils;

class JSON {

	static function respond($var, $options = []) {
		$app = \Katu\App::get();

		$app->response->headers->set('Content-Type', 'application/json; charset=UTF-8');

		if (isset($options['format']) && $options['format'] == 'standard') {
			$body = static::encodeStandard($var);
		} else {
			$body = static::encode($var);
		}

		$app->response->setBody(\Katu\Controller::prepareBody($body));

		return true;
	}

	static function getEncodeBitmask() {
		return
			  (defined('JSON_PRETTY_PRINT')      ? JSON_PRETTY_PRINT      : null)
			| (defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : null)
			| (defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : null)
		;
	}

	static function getInlineEncodeBitmask() {
		return
			  (defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : null)
			| (defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : null)
		;
	}

	static function encode($var) {
		return json_encode($var, static::getEncodeBitmask());
	}

	static function encodeInline($var) {
		return json_encode($var, static::getInlineEncodeBitmask());
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
