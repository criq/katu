<?php

namespace Katu\Utils;

class Cache {

	static function get($name, $callback, $timeout = NULL) {
		if (!defined('TMP_PATH')) {
			throw new Exception("Undefined TMP_PATH.");
		}

		$cache = new \Gregwar\Cache\Cache;
		$cache->setCacheDirectory(TMP_PATH);
		$cache->setPrefixSize(0);

		$opts = array();
		if (isset($timeout) && $timeout) {
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

	static function getURL($url, $timeout = NULL) {
		return \Katu\Utils\Cache::get(array('url', sha1($url)), function() use($url) {

			$curl = new \Curl;
			if ($curl->get($url)) {
				throw new \Katu\Exception("Error getting URL.");
			}

			return $curl->response;

		}, $timeout);
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
