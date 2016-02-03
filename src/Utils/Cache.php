<?php

namespace Katu\Utils;

class Cache {

	static $runtime = [];
	static $memory  = [];

	static function get() {
		if (is_callable(func_get_arg(0))) {
			$name = [];
			@list($callback, $timeout) = func_get_args();
			$args = array_slice(func_get_args(), 2);
		} else {
			@list($name, $callback, $timeout) = func_get_args();
			$args = array_slice(func_get_args(), 3);
		}

		$path = static::getPath((array) $name, (array) $args);

		var_dump($path); die;

		$cache = new \Gregwar\Cache\Cache;
		$cache->setCacheDirectory(static::getCacheDir($path));
		$cache->setPrefixSize(0);

		$opts = [];
		if (isset($timeout) && !is_null($timeout)) {
			$opts['max-age'] = $timeout;
		}

		$callback = function() use($callback, $args) {

			$data = call_user_func_array($callback, $args);
			$serializedData = serialize($data);

			try {

				return gzcompress($serializedData, \Katu\Config::get('app', 'cache', 'compress'));

			} catch (\Katu\Exceptions\MissingConfigException $e) {

				// Do not commpress.
				return $serializedData;

			}

		};

		try {

			$cacheFile = new File($cache->getCacheDirectory(), static::getCacheFile($path));
			$rawData = $cache->getOrCreate(static::getCacheFile($path), $opts, $callback);

			// Try unserialize.
			try {

				$unserializedData = unserialize($rawData);

			} catch (\Exception $e) {

				// It's probably compressed. Try uncompress first.
				try {

					$uncompressedData = gzuncompress($rawData);
					$unserializedData = unserialize($uncompressedData);

				} catch (\Exception $e) {

					// Delete the corrupted file.
					$cacheFile->delete();

					// Try again.
					$rawData = $cache->getOrCreate(static::getCacheFile($path), $opts, $callback);

					// Try unserialize.
					try {

						$unserializedData = unserialize($rawData);

					} catch (\Exception $e) {

						// It's probably compressed. Try uncompress first.
						$uncompressedData = gzuncompress($rawData);
						$unserializedData = unserialize($uncompressedData);

					}

				}


			}

			return $unserializedData;

		} catch (\Katu\Exceptions\DoNotCacheException $e) {

			return $e->data;

		}
	}

	static function reset($name) {
		try {
			return FileSystem::deleteDir(static::getCacheDir(static::getPath($name)));
		} catch (\Exception $e) {
			return false;
		}
	}

	static function getCacheDir($path) {
		return FileSystem::joinPaths(TMP_PATH, dirname($path));
	}

	static function getCacheFile($path) {
		return basename($path);
	}

	static function getPath($name = [], $args = []) {
		// No name, generate it from position in code.
		if (!$name) {
			foreach (debug_backtrace() as $backtrace) {
				if ($backtrace['file'] != __FILE__) {
					$name = array_merge([
						'!anonymous',
						'!' . $backtrace['file'],
						'!' . $backtrace['line'],
					], $args);
					break;
				}
			}
		}

		return FileSystem::getPathForName(array_merge(['!cache'], is_array($name) ? $name : [$name], $args));
	}

	static function getUrl($url, $timeout = null) {
		return \Katu\Utils\Cache::get($url, function() use($url) {

			$response = (new \Katu\Types\TUrl((string) $url))->get($curl);
			if ($curl->error) {
				throw new \Katu\Exceptions\ErrorException("Error getting URL " . ((string) $url));
			}

			if (is_object($response) && is_a($response, 'SimpleXMLElement')) {
				$response = $response->asXML();
			}

			return $response;

		}, $timeout);
	}

	static function getRuntime($name, $callback = null) {
		$args = array_slice(func_get_args(), 2);
		$cacheName = static::getPath((array) $name, (array) $args);

		// There's something cached.
		if (isset(static::$runtime[$cacheName]) && !is_null(static::$runtime[$cacheName])) {
			return static::$runtime[$cacheName];
		}

		// There is callback.
		if (!is_null($callback)) {
			static::$runtime[$cacheName] = call_user_func_array($callback, $args);
			return static::$runtime[$cacheName];
		}

		return null;
	}

	static function resetRuntime($name = null) {
		if ($name) {
			$cacheName = static::getPath($name);
			if (isset(static::$runtime[$cacheName])) {
				unset(static::$runtime[$cacheName]);
			}
		} else {
			static::$runtime = null;
		}

		return true;
	}

	static function getFromMemory($name, $callback = null) {
		return call_user_func_array(['static', 'getFromMemoryWithTtl'], array_merge([$name], [null], [$callback], array_slice(func_get_args(), 2)));
	}

	static function getFromMemoryWithTtl($name, $ttl, $callback = null) {
		$args = array_slice(func_get_args(), 3);
		$cacheName = sha1(var_export(array_merge((array) $name, (array) $args), true));

		// APC supported.
		if (function_exists('apc_add')) {

			if (!apc_exists($cacheName)) {
				apc_add($cacheName, call_user_func_array($callback, $args), (int) $ttl);
			}

			return apc_fetch($cacheName);

		// APC not supported, just use runtime memory.
		} else {

			if (!isset(static::$memory[$cacheName])) {
				static::$memory[$cacheName] = call_user_func_array($callback, $args);
			}

			return static::$memory[$cacheName];

		}
	}

}
