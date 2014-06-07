<?php

namespace Katu\Utils;

class Cache {

	static function get($name, $callback, $timeout = NULL, $options = array()) {
		if (isset($options['dir'])) {
			@mkdir(dirname($dir), 0777, TRUE);
			$dir = $options['dir'];
		} else {
			if (!defined('TMP_PATH')) {
				throw new \Exception("Undefined TMP_PATH.");
			}
			$dir = TMP_PATH;
		}

		$cache = new \Gregwar\Cache\Cache;
		$cache->setCacheDirectory($dir);
		$cache->setPrefixSize(0);

		$opts = array();
		if (isset($timeout) && !is_null($timeout)) {
			$opts['max-age'] = $timeout;
		}

		$callback = function() use($callback) {
			return gzcompress(serialize(call_user_func($callback)), 9);
		};

		return unserialize(gzuncompress($cache->getOrCreate(self::getCacheName($name), $opts, $callback)));
	}

	static function getCacheName($name) {
		return implode('__', (array) $name);
	}

	static function getUrl($url, $timeout = NULL, $options = array()) {
		return \Katu\Utils\Cache::get(array('url', sha1($url)), function() use($url) {

			$curl = new \Curl;
			if ($curl->get($url)) {
				throw new \Katu\Exception("Error getting URL.");
			}

			return $curl->response;

		}, $timeout, $options);
	}

	static function initRuntime() {
		if (!isset($GLOBALS['katu.cache.runtime'])) {
			$GLOBALS['katu.cache.runtime'] = array();
		}

		return TRUE;
	}

	static function setRuntime($name, $value) {
		self::initRuntime();

		$GLOBALS['katu.cache.runtime'][$name] = $value;

		return $value;
	}

	static function getRuntime($name) {
		self::initRuntime();

		if (!isset($GLOBALS['katu.cache.runtime'][$name])) {
			return NULL;
		}

		return $GLOBALS['katu.cache.runtime'][$name];
	}

}
