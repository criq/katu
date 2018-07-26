<?php

namespace Katu;

class Cache {

	const DIR_NAME = 'cache';

	private $name;
	private $timeout;
	private $callback;
	private $args = [];

	private static $memcached;

	public function __construct($name = null, $timeout = null) {
		$this->setName($name);
		$this->setTimeout($timeout);
	}

	public function setName() {
		if (count(func_get_args()) == 1 && is_array(func_get_arg(0)) && func_get_arg(0)) {
			$this->name = func_get_arg(0);
		} else {
			$this->name = array_filter(func_get_args());
		}

		return $this;
	}

	public function getName() {
		return $this->name;
	}

	public function setTimeout($timeout) {
		$this->timeout = $timeout;

		return $this;
	}

	public function getTimeout() {
		return $this->timeout;
	}

	public function getTimeoutInSeconds() {
		$timeout = $this->getTimeout();

		if (is_int($timeout)) {
			return $timeout;
		} elseif (is_float($timeout)) {
			return round($timeout);
		} elseif (is_string($timeout)) {
			return (new \Katu\Utils\DateTime('- ' . $timeout))->getAge();
		}

		return null;
	}

	public function setCallback($callback) {
		$this->callback = $callback;

		return $this;
	}

	public function getCallback() {
		return $this->callback;
	}

	public function setArgs() {
		$this->args = func_get_args();

		return $this;
	}

	public function getArgs() {
		return $this->args;
	}

	public function getRawPathSegments() {
		if ($this->getName()) {
			$pathSegments = $this->getName();
		} else {
			$pathSegments = $this->getAnonymousPathSegments();
		}

		// Add arguments.
		$pathSegments = array_merge($pathSegments, $this->args);

		// Add checksum.
		$pathSegments[] = sha1(serialize($pathSegments));

		return $pathSegments;
	}

	public function getSanitizedPathSegments() {
		$pathSegments = [];

		// Datatype check.
		foreach ($this->getRawPathSegments() as $pathSegment) {
			if (is_string($pathSegment) || is_int($pathSegment) || is_float($pathSegment)) {
				$pathSegments[] = $pathSegment;
			} else {
				$pathSegments[] = sha1(serialize($pathSegment));
			}
		}

		// Sanitize.
		$pathSegments = array_map(function($i) {
			return preg_replace('/[^0-9a-z\-\_\.]/i', '-', $i);
		}, $pathSegments);

		return $pathSegments;
	}

	public function getAnonymousPathSegments() {
		$backtraceIndex = 5;

		return array_merge(['anonymous'], array_values(array_filter(explode('/', debug_backtrace()[$backtraceIndex]['file']))), ['line ' . debug_backtrace()[$backtraceIndex]['line']]);
	}

	public function getFile() {
		return new \Katu\Utils\File(TMP_PATH, static::DIR_NAME, implode('/', $this->getSanitizedPathSegments()), ['cache']);
	}

	public function getMemoryKey() {
		$key = implode('.', $this->getSanitizedPathSegments());
		$key = (new \Katu\Types\TString($key))->normalizeSpaces();
		$key = preg_replace('/\s/', null, $key);
		if (mb_strlen($key) > 250) {
			$key = sha1($key);
		}

		return $key;
	}

	static function isApcSupported() {
		try {
			return \Katu\Config::get('app', 'cache', 'supported', 'apc') && function_exists('apc_exists');
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			return false;
		}
	}

	static function getMaxApcSize() {
		$size = \Katu\Utils\FileSize::createFromIni(ini_get('apc.max_file_size'))->size ?: 1024 * 1024;

		return $size * .75;
	}

	static function isMemcachedSupported() {
		try {
			return \Katu\Config::get('app', 'cache', 'supported', 'memcached') && class_exists('Memcached');
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			return false;
		}
	}

	static function getMemcahcedInstanceName() {
		return 'appCache';
	}

	static function loadMemcached() {
		if (!static::$memcached) {
			static::$memcached = new \Memcached(static::getMemcahcedInstanceName());
			static::$memcached->addServer('localhost', 11211);
		}

		return true;
	}

	public function getMemcached() {
		static::loadMemcached();

		return static::$memcached;
	}

	public function getResult() {
		$memoryKey = $this->getMemoryKey();

		// Try Memcached.
		if (static::isMemcachedSupported()) {

			$memcached = $this->getMemcached();
			$res = $memcached->get($memoryKey);
			if ($memcached->getResultCode() === \Memcached::RES_SUCCESS) {
				return $res;
			}

		// Try APC.
		} elseif (static::isApcSupported()) {

			if (apc_exists($memoryKey)) {
				$res = apc_fetch($memoryKey, $success);
				if ($success) {
					return $res;
				}
			}

		}

		// Try file.
		$file = $this->getFile();
		if ($file->exists() && \Katu\Utils\DateTime::createFromTimestamp(filemtime($file))->getAge() <= $this->getTimeoutInSeconds()) {
			return unserialize($file->get());
		}

		// Get result.
		try {
			$res = call_user_func_array($this->getCallback(), $this->getArgs());
		} catch (\Katu\Exceptions\DoNotCacheException $e) {
			return $e->data;
		}

		// Try to save into Memcached.
		if (static::isMemcachedSupported()) {

			// Add to Memcached.
			$memcached = $this->getMemcached();
			try {
				$timeout = $this->getTimeoutInSeconds();
				if (!$memcached->set($memoryKey, $res, $timeout ? time() + $timeout : 0)) {
					throw new \Exception;
				}
				return $res;
			} catch (\Exception $e) {
				$memcached->delete($memoryKey);
			}

		// Try to save into APC.
		} elseif (static::isApcSupported() && strlen(serialize($res)) <= static::getMaxApcSize()) {

			// Add to APC.
			try {
				if (!apc_store($memoryKey, $res, $this->getTimeoutInSeconds())) {
					throw new \Exception;
				}
				return $res;
			} catch (\Exception $e) {
				apc_delete($memoryKey);
			}

		}

		// Try to save into file.
		$this->getFile()->set(serialize($res));
		return $res;
	}

	static function clearMemory() {
		if (static::isMemcachedSupported()) {
			static::loadMemcached();
			return static::$memcached->flush();
		}
		if (static::isApcSupported()) {
			return apc_clear_cache();
		}

		return null;
	}

	/*****************************************************************************
	 * Code sugar.
	 */

	static function get() {
		$args = func_get_args();

		$object = new static($args[0], $args[1]);
		$object->setCallback($args[2]);
		call_user_func_array([$object, 'setArgs'], array_slice($args, 3));

		return $object->getResult();
	}

}
