<?php

namespace Katu\Cache;

class General
{
	const DIR_NAME = 'cache';

	protected $args = [];
	protected $callback;
	protected $enableApcu = true;
	protected $enableMemcached = true;
	protected $enableRedis = true;
	protected $name;
	protected $timeout;
	protected static $memcached;

	public function __construct($name = null, $timeout = null, ?callable $callback = null)
	{
		$this->setName($name);
		$this->setTimeout($timeout);

		if ($callback) {
			$this->setCallback($callback);
		}
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

	public static function parseTimeout($timeout)
	{
		if (is_int($timeout)) {
			return $timeout;
		} elseif (is_float($timeout)) {
			return round($timeout);
		} elseif (is_string($timeout)) {
			return (new \Katu\Tools\DateTime\DateTime('- ' . $timeout))->getAge();
		}

		return false;
	}

	public function getTimeoutInSeconds()
	{
		return static::parseTimeout($this->getTimeout());
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

	public function disableMemory()
	{
		$this->enableApcu = false;
		$this->enableMemcached = false;

		return $this;
	}

	public static function generateMemoryKey()
	{
		$key = \Katu\Files\File::generatePath(array_merge([\Katu\Config\Env::getHash()], func_get_args()));
		if (mb_strlen($key) > 250) {
			$key = sha1($key);
		}

		return $key;
	}

	public function getMemoryKey()
	{
		return static::generateMemoryKey([
			$this->name,
			$this->args,
		]);
	}

	public function getFile() : \Katu\Files\File
	{
		return new \Katu\Files\File(\Katu\App::getTemporaryDir(), static::DIR_NAME, \Katu\Files\File::generatePath([
			$this->name,
			$this->args,
		], 'txt'));
	}

	/****************************************************************************
	 * APC.
	 */
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

	/****************************************************************************
	 * Memcached.
	 */
	public static function isMemcachedSupported()
	{
		try {
			return class_exists('Memcached');
		} catch (\Throwable $e) {
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

	/****************************************************************************
	 * Redis.
	 */
	public static function isRedisSupported()
	{
		try {
			$client = new \Predis\Client;
			$client->connect();
			return $client->isConnected();
		} catch (\Throwable $e) {
			return false;
		}
	}

	public function isRedisEnabled()
	{
		return $this->enableRedis && static::isRedisSupported();
	}

	public function getRedis()
	{
		return new \Predis\Client;
	}



	public function getResult()
	{
		$memoryKey = $this->getMemoryKey();

		// Try Redis.
		if ($this->isRedisEnabled()) {
			// (new \Katu\Tools\Logs\Logger('cache'))->log('debug', "Redis is enabled.");
			$redis = $this->getRedis();
			if ($redis->exists($memoryKey)) {
				(new \Katu\Tools\Logs\Logger('cache'))->log('debug', "Key '$memoryKey' found in Redis.");
				return unserialize($redis->get($memoryKey));
			}
		} else {
			// (new \Katu\Tools\Logs\Logger('cache'))->log('debug', "Redis is not enabled.");
		}

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
		if ($file->exists() && \Katu\Tools\DateTime\DateTime::createFromTimestamp(filemtime($file))->getAge() <= $this->getTimeoutInSeconds()) {
			return unserialize($file->get());
		}

		// Get result.
		$res = call_user_func_array($this->getCallback(), $this->getArgs());
		$serializedRes = serialize($res);

		// Try to save into Redis.
		if ($this->isRedisEnabled()) {
			// (new \Katu\Tools\Logs\Logger('cache'))->log('debug', "Redis is enabled.");
			// Add to Redis.
			$redis = $this->getRedis();
			try {
				$redis->set($memoryKey, serialize($res));
				(new \Katu\Tools\Logs\Logger('cache'))->log('debug', "Memory key '$memoryKey' written to Redis.");
				$timeout = $this->getTimeoutInSeconds();
				if ($timeout) {
					$redis->expire($memoryKey, $timeout);
					(new \Katu\Tools\Logs\Logger('cache'))->log('debug', "Memory key '$memoryKey' in Redis set to expire in $timeout seconds.");
				}
				return $res;
			} catch (\Throwable $e) {
				(new \Katu\Tools\Logs\Logger('cache'))->error($e);
				$redis->del($memoryKey);
			}
		} else {
			// (new \Katu\Tools\Logs\Logger('cache'))->log('debug', "Redis is not enabled.");
		}

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
			} catch (\Throwable $e) {
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
		try {
			$redis = new \Predis\Client;
			$redis->flushall();
		} catch (\Throwable $e) {
			// Nevermind.
		}

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

	public function exists() : bool
	{
		$memoryKey = $this->getMemoryKey();

		// Try Memcached.
		if ($this->isMemcachedEnabled()) {
			$memcached = $this->getMemcached();
			$memcached->get($memoryKey);
			if ($memcached->getResultCode() === \Memcached::RES_SUCCESS) {
				return true;
			}
		}

		// Try APC.
		if ($this->isApcEnabled() && \apcu_exists($memoryKey)) {
			$success = null;
			\apcu_fetch($memoryKey, $success);
			if ($success) {
				return true;
			}
		}

		// Try file.
		$file = $this->getFile();
		if ($file->exists() && \Katu\Tools\DateTime\DateTime::createFromTimestamp(filemtime($file))->getAge() <= $this->getTimeoutInSeconds()) {
			return true;
		}

		return false;
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
