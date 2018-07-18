<?php

namespace Katu;

class Cache {

	const DIR_NAME = 'cache';
	const APC__MAX_SIZE = 524288;

	private $name;
	private $timeout;
	private $callback;
	private $args = [];

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
		return sha1(serialize([
			static::DIR_NAME,
			$this->getSanitizedPathSegments(),
		]));
	}

	static function isApcSupported() {
		return function_exists('apc_exists') && function_exists('apc_fetch') && function_exists('apc_store') && function_exists('apc_clear_cache');
	}

	public function getResult() {
		$memoryKey = $this->getMemoryKey();

		// Try memory.
		if (static::isApcSupported()) {

			if (apc_exists($memoryKey)) {
				$res = apc_fetch($memoryKey, $success);
				if ($success) {
					return $res;
				}
			}

		}

		// Try file.
		$file = $this->getFile();
		if ($file->exists() && \Katu\Utils\DateTime::createFromTimestamp(filemtime($file))->getAge() <= $this->getTimeout()) {
			return unserialize($file->get());
		}

		// Get result.
		try {
			$res = call_user_func_array($this->getCallback(), $this->getArgs());
		} catch (\Katu\Exceptions\DoNotCacheException $e) {
			return $e->data;
		}

		// Try to save in memory.
		if (static::isApcSupported() && strlen(serialize($res)) <= static::APC__MAX_SIZE) {

			// Add to memory.
			apc_store($memoryKey, $res, $this->getTimeoutInSeconds());
			return $res;

		// Try to save into file.
		} else {

			$this->getFile()->set(serialize($res));
			return $res;

		}

	}

	static function clearMemory() {
		if (static::isApcSupported()) {
			return apc_clear_cache();
		}

		return null;
	}

	// Code sugar.
	static function get($name, $timeout, $callback, $args = []) {
		$object = new static($name, $timeout);
		$object->setCallback($callback);
		call_user_func_array([$object, 'setArgs'], array_slice(func_get_args(), 3));

		return $object->getResult();
	}

}
