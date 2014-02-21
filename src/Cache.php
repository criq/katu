<?php

namespace Jabli\Aids;

class Cache {

	static function getArray($name, $callback, $timeout = NULL) {
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

		return JSON::decodeAsArray($cache->getOrCreate(self::getCacheName($name), $opts, $callback));
	}

	static function getCacheName($name) {
		return implode('__', (array) $name);
	}

}
