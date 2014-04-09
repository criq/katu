<?php

namespace Jabli\Utils;

class URL {

	static function isHTTPS() {
		return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on';
	}

	static function getCurrent() {
		return new \Jabli\Types\URL('http' . (self::isHTTPS() ? 's' : NULL) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
	}

	static function getBase() {
		return new \Jabli\Types\URL(\Jabli\Config::getApp('base_url'));
	}

	static function getSite($uri) {
		return new \Jabli\Types\URL(self::joinPaths(self::getBase(), $uri));
	}

	static function joinPaths() {
		return implode('/', array_map(function($i){
			return trim($i, '/');
		}, func_get_args()));
	}

}
