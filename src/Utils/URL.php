<?php

namespace Jabli\Utils;

class URL {

	static function joinPaths() {
		return implode('/', array_map(function($i){
			return trim($i, '/');
		}, func_get_args()));
	}

	static function getCurrent() {
		$app = \Jabli\FW::getApp();

		return $app->request->getUrl() . $app->request->getPath();
	}

	static function getBase() {
		return \Jabli\Config::get('base_url');
	}

	static function getSite($uri) {
		return self::joinPaths(self::getBase(), $uri);
	}

}
