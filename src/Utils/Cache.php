<?php

namespace Katu\Utils;

class Cache {

	static function get($name, $callback, $timeout = null, $options = []) {
		$cacheName = self::getCacheName($name);

		$cache = new \Gregwar\Cache\Cache;
		$cache->setCacheDirectory(static::getCacheDir($cacheName));
		$cache->setPrefixSize(0);

		$opts = [];
		if (isset($timeout) && !is_null($timeout)) {
			$opts['max-age'] = $timeout;
		}

		$callback = function() use($callback) {
			return gzcompress(serialize(call_user_func($callback)), 9);
		};

		try {
			return unserialize(gzuncompress($cache->getOrCreate(static::getCacheFile($cacheName), $opts, $callback)));
		} catch (\Katu\Exceptions\DoNotCacheException $e) {
			return $e->data;
		}
	}

	static function getCacheName($name) {
		$path = implode('/', array_map(function($i) {
			if (is_string($i) || is_int($i) || is_float($i) || is_bool($i)) {
				return (string) $i;
			}
			return sha1(serialize($i));
		}, (array) $name));

		$path = trim($path, '/');

		return $path;
	}

	static function getCacheDir($cacheName) {
		return FS::joinPaths(TMP_PATH, dirname($cacheName));
	}

	static function getCacheFile($cacheName) {
		return basename($cacheName);
	}

	static function getUrl($url, $timeout = null, $options = []) {
		$tUrl = new \Katu\Types\TUrl($url);
		$parts = $tUrl->getParts();
		$path = trim(implode('/', array_map(function($i) {
			return trim($i, '/');
		}, array_filter([
			'url',
			$parts['scheme'],
			$parts['host'],
			$parts['path'],
			isset($parts['query']) ? sha1(serialize($parts['query'])) : null,
			'.cache',
		]))), '/');

		$path = preg_replace('#/+#', '/', $path);

		return \Katu\Utils\Cache::get($path, function() use($url) {

			$url = new \Katu\Types\TUrl((string) $url);
			$response = $url->get($curl);

			if ($curl->error) {
				throw new \Katu\Exceptions\ErrorException("Error getting URL.");
			}

			if (is_object($response) && is_a($response, 'SimpleXMLElement')) {
				$response = $response->asXML();
			}

			return $response;

		}, $timeout, $options);
	}

	static function initRuntime() {
		if (!isset($GLOBALS['katu.cache.runtime'])) {
			$GLOBALS['katu.cache.runtime'] = [];
		}

		return true;
	}

	static function setRuntime($name, $value) {
		self::initRuntime();

		$GLOBALS['katu.cache.runtime'][$name] = $value;

		return $value;
	}

	static function getRuntime($name) {
		self::initRuntime();

		if (!isset($GLOBALS['katu.cache.runtime'][$name])) {
			return null;
		}

		return $GLOBALS['katu.cache.runtime'][$name];
	}

}
