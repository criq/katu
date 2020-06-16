<?php

namespace Katu\Cache;

class General
{
	const DIR_NAME = 'cache';

	protected $args = [];
	protected $callback;
	protected $enableApcu = true;
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
		$args = func_get_args();

		if (count($args) == 1 && is_array($args[0]) && $args[0]) {
			$name = func_get_arg(0);
		} else {
			$name = $args;
		}

		$this->name = $name;

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
			return (new \Katu\Tools\DateTime\DateTime('- ' . $timeout))->getAge();
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

	public function getArgs() : array
	{
		return (array)$this->args;
	}

	public function getKeyArray() : array
	{
		$array = [
			$this->name,
			$this->args,
		];
		// var_dump($array);die;

		$checksum = crc32(serialize($array));
		// var_dump($checksum);die;

		$array = (new \Katu\Types\TArray($array))->flatten()->getArray();
		// var_dump($array);die;

		$array = array_map(function ($i) {
			try {
				return (string)$i;
			} catch (\Throwable $e) {
				return sha1(serialize($i));
			}
		}, $array);
		// var_dump($array);die;

		$array = array_map(function ($i) {
			$i = strtr($i, '\\', '/');
			$i = strtr($i, '.', '_');
			$i = mb_strtolower($i);
			$i = preg_replace('/[^a-z0-9_\\/]/i', null, $i);
			return $i;
		}, $array);
		// var_dump($array);die;

		$array[] = 'crc32_' . $checksum;

		return (array)$array;
	}

	public function getMemoryKey()
	{
		$key = implode('.', $this->getKeyArray());
		if (mb_strlen($key) > 250) {
			$key = sha1($key);
		}

		return $key;
	}

	public function getFile() : \Katu\Files\File
	{
		$array = $this->getKeyArray();
		$array[] = 'cache.txt';

		$file = new \Katu\Files\File(\Katu\App::getTemporaryDir(), static::DIR_NAME, ...$array);
		// var_dump($file);

		return $file;
	}

	public static function isApcSupported()
	{
		try {
			return function_exists('apcu_exists');
		} catch (\Throwable $e) {
			return false;
		}
	}

	public function isApcEnabled()
	{
		return $this->enableApcu && static::isApcSupported();
	}

	public static function getMaxApcSize()
	{
		$size = \Katu\Files\Size::createFromIni(ini_get('apc.max_file_size'))->size ?: 1024 * 1024;

		return $size * .75;
	}

	public static function isMemcachedSupported()
	{
		try {
			return class_exists('Memcached');
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
			static::$memcached = new \Memcached;
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
		}

		// Try APC.
		if ($this->isApcEnabled() && \apcu_exists($memoryKey)) {
			$success = null;
			$res = \apcu_fetch($memoryKey, $success);
			if ($success) {
				return $res;
			}
		}

		// Try file.
		$file = $this->getFile();
		// var_dump($file);die;
		if ($file->exists() && \Katu\Tools\DateTime\DateTime::createFromTimestamp(filemtime($file))->getAge() <= $this->getTimeoutInSeconds()) {
			return unserialize($file->get());
		}

		// Get result.
		$res = call_user_func_array($this->getCallback(), $this->getArgs());
		$serializedRes = serialize($res);

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
		}

		// Try to save into APC.
		if ($this->isApcEnabled() && strlen($serializedRes) <= static::getMaxApcSize()) {
			// Add to APC.
			try {
				if (!\apcu_store($memoryKey, $res, $this->getTimeoutInSeconds())) {
					throw new \Exception;
				}
				return $res;
			} catch (\Throwable $e) {
				\apcu_delete($memoryKey);
			}
		}

		// Try to save into file.
		$this->getFile()->set($serializedRes);

		return $res;
	}

	public function clear()
	{
		$memoryKey = $this->getMemoryKey();

		// APC.
		if ($this->isApcEnabled() && \apcu_exists($memoryKey)) {
			\apcu_delete($memoryKey);
		}

		// File.
		$file = $this->getFile();
		if ($file->exists()) {
			$file->delete();
		}

		return true;
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

	/****************************************************************************
	 * Code sugar.
	 */
	public static function get()
	{
		$args = func_get_args();

		$cache = new static($args[0], $args[1]);
		if (isset($args[2])) {
			$cache->setCallback($args[2]);
		}

		$cache->setArgs(...(array)array_slice($args, 3));

		return $cache->getResult();
	}
}
