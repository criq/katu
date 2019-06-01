<?php

namespace Katu\Tools\Routing;

use \Katu\App;
use \Katu\Types\TURL;

class URL {

	static function isHttps() {
		return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on';
	}

	static function getCurrent() {
		return new TURL('http' . (self::isHttps() ? 's' : null) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
	}

	static function getBase() {
		return new TURL(\Katu\Config\Config::getApp('baseUrl'));
	}

	static function getFor($handle, $args = [], $params = []) {
		$app = App::get();

		return TURL::make(self::joinPaths(self::getBase()->getHostWithScheme(), $app->urlFor($handle, array_map('urlencode', (array)$args))), $params);
	}

	static function getDecodedFor($handle, $args = [], $params = []) {
		$app = App::get();

		return TURL::make(self::joinPaths(self::getBase()->getHostWithScheme(), $app->urlFor($handle, $args)), $params);
	}

	static function joinPaths() {
		return implode('/', array_map(function($i){
			return trim($i, '/');
		}, func_get_args()));
	}

}
