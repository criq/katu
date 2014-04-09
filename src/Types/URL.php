<?php

namespace Jabli\Types;

class URL {

	const DEFAULT_SCHEME = 'http';

	public $value;

	public function __construct($value) {
		if (!self::isValid($value)) {
			throw new Exception("Invalid URL.");
		}

		$this->value = (string) (trim($value));
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



	static function isValid($value) {
		return filter_var(trim($value), FILTER_VALIDATE_URL) !== FALSE;
	}

	static function joinPaths() {
		return implode('/', array_map(function($i){
			return trim($i, '/');
		}, func_get_args()));
	}



	public function addParam($name, $value, $overwrite = TRUE) {
		$parts = parse_url($this->value);

		if (!$overwrite && isset($parts['query'][$name])) {
			throw new Exception("Query param already exists.");
		}

		$parts['query'][$name] = $value;

		$this->value = self::buildURL($parts);

		return TRUE;
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

	static function buildURL($parts) {
		$url = '';

		if (!isset($parts['host'])) {
			throw new Exception("Missing host");
		}

		if (!isset($parts['scheme'])) {
			$url .= self::DEFAULT_SCHEME;
		} else {
			$url .= $parts['scheme'];
		}

		$url .= '://' . $parts['host'];

		if (isset($parts['path'])) {
			$url .= $parts['path'];
		}

		if (isset($parts['query'])) {
			$url .= '?' . http_build_query($parts['query']);
		}

		return $url;
	}

}
