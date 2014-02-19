<?php

namespace Jabli\Aids;

class Cache {

	static function getArray($name, $callback) {
		if (!defined('TMP_PATH')) {
			throw new Exception("Undefined tmp folder.");
		}

		$cache = new \Gregwar\Cache\Cache;
		$cache->setCacheDirectory(TMP_PATH);
		$cache->setPrefixSize(0);

		return JSON::decodeAsArray($cache->getOrCreate(self::getCacheName($name), array('max-age' => 1), $callback));
	}

	static function getCacheName($name) {
		return implode('__', (array) $name);
	}

}
