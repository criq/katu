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
		return \Jabli\Config::getApp('base_url');
	}

	static function getSite($uri) {
		return self::joinPaths(self::getBase(), $uri);
	}

	static function make($url, $params = array()) {
		return $url . ($params ? '?' . http_build_query($params) : NULL);
	}

	static function get2ndLevelDomain($url) {
		$parsed = parse_url($url);
		if (!isset($parsed['host'])) {
			throw new \Jabli\Exception("Invalid URL host.");
		}

		return implode('.', array_slice(explode('.', $parsed['host']), -2));
	}

}
