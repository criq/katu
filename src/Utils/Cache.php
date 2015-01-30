<?php

namespace Katu\Utils;

class Cache {

	static function get() {
		if (is_callable(func_get_arg(0))) {
			$name = null;
			@list($callback, $timeout, $options) = func_get_args();
		} else {
			@list($name, $callback, $timeout, $options) = func_get_args();
		}

		// No name, generate it from position in code.
		if (!$name) {
			var_dump(func_get_args());
			foreach (debug_backtrace() as $backtrace) {
				if ($backtrace['file'] != __FILE__) {
					$name = [
						'anonymous',
						$backtrace['file'],
						$backtrace['line'],
					];
					break;
				}
			}
		}

		$path = FS::getPathForName($name);

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
