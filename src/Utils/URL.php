<?php

namespace Katu\Utils;

class URL {

	static function isHTTPS() {
		return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on';
	}

	static function getCurrent() {
		return new \Katu\Types\TURL('http' . (self::isHTTPS() ? 's' : NULL) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
	}

	static function getBase() {
		return new \Katu\Types\TURL(\Katu\Config::getApp('baseURL'));
	}

	static function getSite($uri) {
		return new \Katu\Types\TURL(self::joinPaths(self::getBase(), $uri));
	}

	static function getFor($handle) {
		$app = \Katu\App::get();

		return new \Katu\Types\TURL(self::joinPaths(self::getBase()->getHostWithProtocol(), $app->urlFor($handle)));
	}

	static function joinPaths() {
		return implode('/', array_map(function($i){
			return trim($i, '/');
		}, func_get_args()));
	}

}
