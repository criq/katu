<?php

namespace Katu;

class Cache
{
	const DIR_NAME = 'cache';

	protected $args = [];
	protected $callback;
	protected $enableApc = true;
	protected $enableMemcached = true;
	protected $name;
	protected $timeout;
	protected static $memcached;

	public function __construct($name = null, $timeout = null)
	{
		$this->setName($name);
		$this->setTimeout($timeout);
	}

	public function setName()
	{
		if (count(func_get_args()) == 1 && is_array(func_get_arg(0)) && func_get_arg(0)) {
			$this->name = func_get_arg(0);
		} else {
			$this->name = array_filter(func_get_args());
		}

		return $this;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setTimeout($timeout)
	{
		$this->timeout = $timeout;

		return $this;
	}

	public function getTimeout()
	{
		return $this->timeout;
	}

	public function getTimeoutInSeconds()
	{
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

	public function setCallback($callback)
	{
		$this->callback = $callback;

		return $this;
	}

	public function getCallback()
	{
		return $this->callback;
	}

	public function setArgs()
	{
		$this->args = func_get_args();

		return $this;
	}

	public function getArgs()
	{
		return $this->args;
	}

	public function getRawPathSegments()
	{
		if ($this->getName()) {
			$pathSegments = array_map(function ($i) {
				return new Cache\Path\Raw($i);
			}, $this->getName());
		} else {
			$pathSegments = $this->getAnonymousPathSegments();
		}

		// Add arguments.
		foreach ($this->args as $arg) {
			$pathSegments[] = new Cache\Path\Raw($arg);
		}

		// Add checksum.
		$pathSegments[] = new Cache\Path\Checksum(sha1(serialize($pathSegments)));

		return $pathSegments;
	}

	public function getAnonymousPathSegments()
	{
		$backtraceIndex = 5;

		$array = array_merge(['anonymous'], array_values(array_filter(explode('/', debug_backtrace()[$backtraceIndex]['file']))), ['line ' . debug_backtrace()[$backtraceIndex]['line']]);

		return array_map(function ($i) {
			return new Cache\Path\Raw($i);
		}, $array);
	}

	public function getSanitizedPathSegments()
	{
		return array_map(function ($i) {
			return (string)$i;
		}, $this->getRawPathSegments());
	}

	public function getSanitizedPath()
	{
		return implode('/', $this->getSanitizedPathSegments());
	}

	public function getFile()
	{
		return new \Katu\Utils\File(TMP_PATH, static::DIR_NAME, $this->getSanitizedPath(), ['cache']);
	}

	public function getMemoryKey()
	{
		$key = $this->getSanitizedPath();
		if (mb_strlen($key) > 250) {
			$key = sha1($key);
		}

		return $key;
	}

	public static function isApcSupported()
	{
		try {
			return \Katu\Config::get('app', 'cache', 'supported', 'apc') && function_exists('apcu_exists');
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			return false;
		}
	}

	public function isApcEnabled()
	{
		return $this->enableApc && static::isApcSupported();
	}

	public static function getMaxApcSize()
	{
		$size = \Katu\Utils\FileSize::createFromIni(ini_get('apc.max_file_size'))->size ?: 1024 * 1024;

		return $size * .75;
	}

	public static function isMemcachedSupported()
	{
		try {
			return \Katu\Config::get('app', 'cache', 'supported', 'memcached') && class_exists('Memcached');
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			return false;
		}
	}

	public function isMemcachedEnabled()
	{
		return $this->enableMemcached && static::isMemcachedSupported();
	}

	public static function getMemcahcedInstanceName()
	{
		return 'appCache';
	}

	public static function loadMemcached()
	{
		if (!static::$memcached) {
			static::$memcached = new \Memcached(static::getMemcahcedInstanceName());
			static::$memcached->addServer('localhost', 11211);
		}

		return true;
	}

	public function getMemcached()
	{
		static::loadMemcached();

		return static::$memcached;
	}

	public function getResult()
	{
		$memoryKey = $this->getMemoryKey();

		// Try Memcached.
		if ($this->isMemcachedEnabled()) {
			$memcached = $this->getMemcached();
			$res = $memcached->get($memoryKey);
			if ($memcached->getResultCode() === \Memcached::RES_SUCCESS) {
				return $res;
			}

		// Try APC.
		} elseif ($this->isApcEnabled()) {
			if (\apcu_exists($memoryKey)) {
				$res = \apcu_fetch($memoryKey, $success);
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
		$res = call_user_func_array($this->getCallback(), $this->getArgs());

		// Try to save into Memcached.
		if ($this->isMemcachedEnabled()) {
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
		} elseif ($this->isApcEnabled() && strlen(serialize($res)) <= static::getMaxApcSize()) {
			// Add to APC.
			try {
				if (!\apcu_store($memoryKey, $res, $this->getTimeoutInSeconds())) {
					throw new \Exception;
				}
				return $res;
			} catch (\Exception $e) {
				\apcu_delete($memoryKey);
			}
		}

		// Try to save into file.
		$this->getFile()->set(serialize($res));
		return $res;
	}

	public static function clearMemory()
	{
		if (static::isMemcachedSupported()) {
			static::loadMemcached();
			return static::$memcached->flush();
		}
		if (static::isApcSupported()) {
			\apcu_clear_cache();
			return true;
		}

		return null;
	}

	public static function get()
	{
		$args = func_get_args();

		$object = new static($args[0], $args[1]);
		if (isset($args[2])) {
			$object->setCallback($args[2]);
		}

		call_user_func_array([$object, 'setArgs'], (array)array_slice($args, 3));

		return $object->getResult();
	}
}
