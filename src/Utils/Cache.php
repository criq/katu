<?php

namespace Katu\Utils;

class Cache {

	static function get() {
		if (is_callable(func_get_arg(0))) {
			$name = [];
			@list($callback, $timeout, $options) = func_get_args();
		} else {
			@list($name, $callback, $timeout, $options) = func_get_args();
		}

		// No name, generate it from position in code.
		if (!$name) {
			foreach (debug_backtrace() as $backtrace) {
				if ($backtrace['file'] != __FILE__) {
					$name = [
						'!anonymous',
						'!' . $backtrace['file'],
						'!' . $backtrace['line'],
						$options,
					];
					break;
				}
			}
		}

		$path = FileSystem::getPathForName(array_merge(['!cache'], is_array($name) ? $name : [$name]));

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
		return FileSystem::joinPaths(TMP_PATH, dirname($path));
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

	static function getName($name) {
		return implode('.', is_array($name) ? $name : [$name]);
	}

	static function getRuntime($name, $callback = null) {
		// Init global runtime cache.
		if (!isset($GLOBALS['katu.cache.runtime'])) {
			$GLOBALS['katu.cache.runtime'] = [];
		}

		$cacheName = static::getName($name);

		// There's something cached.
		if (isset($GLOBALS['katu.cache.runtime'][$cacheName]) && !is_null($GLOBALS['katu.cache.runtime'][$cacheName])) {
			return $GLOBALS['katu.cache.runtime'][$cacheName];
		}

		// There is callback.
		if (!is_null($callback)) {
			$GLOBALS['katu.cache.runtime'][$cacheName] = call_user_func($callback);
			return $GLOBALS['katu.cache.runtime'][$cacheName];
		}

		return null;
	}

}
