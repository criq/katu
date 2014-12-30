<?php

namespace Katu\Utils;

use \Katu\App;
use \Katu\Config;
use \Katu\Types\TUrl;

class Url {

	static function isHttps() {
		return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on';
	}

	static function getCurrent() {
		return new TUrl('http' . (self::isHttps() ? 's' : null) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
	}

	static function getBase() {
		return new TUrl(Config::getApp('baseUrl'));
	}

	static function getSite($uri) {
		return new TUrl(self::joinPaths(self::getBase(), $uri));
	}

	static function getFor($handle, $args = [], $params = []) {
		$app = App::get();

		return TUrl::make(self::joinPaths(self::getBase()->getHostWithScheme(), $app->urlFor($handle, array_map('urlencode', (array) $args))), $params);
	}

	static function joinPaths() {
		return implode('/', array_map(function($i){
			return trim($i, '/');
		}, func_get_args()));
	}

}
