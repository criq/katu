<?php

namespace Katu\Utils;

class Cache {

	static function get($name, $callback, $timeout = null, $options = array()) {
		if (isset($options['dir'])) {
			@mkdir(dirname($dir), 0777, true);
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

		try {
			return unserialize(gzuncompress($cache->getOrCreate(self::getCacheName($name), $opts, $callback)));
		} catch (\Katu\Exceptions\DoNotCacheException $e) {
			return $e->data;
		}
	}

	static function getCacheName($name) {
		return implode('__', (array) $name);
	}

	static function getUrl($url, $timeout = null, $options = array()) {
		return \Katu\Utils\Cache::get(array('url', sha1($url)), function() use($url) {

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
			$GLOBALS['katu.cache.runtime'] = array();
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
