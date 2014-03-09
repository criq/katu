<?php

namespace Jabli\Utils;

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
			return gzcompress(JSON::encode(call_user_func($callback)), 9);
		};

		return JSON::decodeAsArray(gzuncompress($cache->getOrCreate(self::getCacheName($name), $opts, $callback)));
	}

	static function getCacheName($name) {
		return implode('__', (array) $name);
	}

}
