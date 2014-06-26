<?php

namespace Katu\Utils;

use \Katu\App;
use \Katu\Config;
use \Katu\Types\TURL;

class Url {

	static function isHttps() {
		return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on';
	}

	static function getCurrent() {
		return new TURL('http' . (self::isHttps() ? 's' : NULL) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
	}

	static function getBase() {
		return new TURL(Config::getApp('baseUrl'));
	}

	static function getSite($uri) {
		return new TURL(self::joinPaths(self::getBase(), $uri));
	}

	static function getFor($handle, $args = array()) {
		$app = App::get();

		return new TURL(self::joinPaths(self::getBase()->getHostWithProtocol(), $app->urlFor($handle, array_map('urlencode', $args))));
	}

	static function joinPaths() {
		return implode('/', array_map(function($i){
			return trim($i, '/');
		}, func_get_args()));
	}

}
