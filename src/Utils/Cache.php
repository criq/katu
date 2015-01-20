<?php

namespace Katu\Utils;

class Cache {

	static function get($name, $callback, $timeout = null, $options = []) {
		$path = self::getCachePath($name);

		$cache = new \Gregwar\Cache\Cache;
		$cache->setCacheDirectory(static::getCacheDir($path));
		$cache->setPrefixSize(0);

		$opts = [];
		if (isset($timeout) && !is_null($timeout)) {
			$opts['max-age'] = $timeout;
		}

		$callback = function() use($callback) {
			return gzcompress(serialize(call_user_func($callback)), 9);
		};

		try {
			return unserialize(gzuncompress($cache->getOrCreate(static::getCacheFile($path), $opts, $callback)));
		} catch (\Katu\Exceptions\DoNotCacheException $e) {
			return $e->data;
		}
	}

	static function getCachePath($name) {
		$name = is_array($name) ? $name : [$name];
		$segments = [];

		// URL.
		foreach ($name as $namePart) {

			try {
				$parts = (new \Katu\Types\TUrl($namePart))->getParts();
				$segments[] = $parts['scheme'];
				$segments[] = $parts['host'];
				$segments[] = $parts['path'];
				$segments[] = $parts['query'];
			} catch (\Exception $e) {
				$segments[] = $namePart;
			}

		}

		// Explode "/" hashes into segments.
		$_segments = [];
		foreach ($segments as $segment) {
			if (is_string($segment)) {
				foreach (explode('/', trim($segment, '/')) as $e) {
					$_segments[] = $e;
				}
			} else {
				$_segments[] = sha1(serialize($segment));
			}
		}

		$segments = $_segments;

		// Sanitize.
		foreach ($segments as &$segment) {
			$segment = preg_replace('#[^a-z0-9\.\-_]#i', '_', $segment);
		}

		// Attach hashed hidden file name at the end.
		$segments[] = '.' . sha1(serialize($name));

		$path = implode('/', $segments);

		return $path;
	}

	static function getCacheDir($path) {
		return FS::joinPaths(TMP_PATH, dirname($path));
	}

	static function getCacheFile($path) {
		return basename($path);
	}

	static function getUrl($url, $timeout = null, $options = []) {
		return \Katu\Utils\Cache::get($url, function() use($url) {

			$response = (new \Katu\Types\TUrl((string) $url))->get($curl);
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
