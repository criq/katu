<?php

namespace Katu\Cache;

use Katu\Tools\DateTime\Timeout;
use Katu\Types\TIdentifier;

class General
{
	const DIR_NAME = 'cache';

	protected $args = [];
	protected $callback;
	protected $enableApcu = true;
	protected $enableMemcached = true;
	protected $enableRedis = true;
	protected $identifier;
	protected $timeout;
	protected static $memcached;
	protected static $redis;

	public function __construct(TIdentifier $identifier, Timeout $timeout, ?callable $callback = null)
	{
		$this->setIdentifier($identifier);
		$this->setTimeout($timeout);
		$this->setCallback($callback);
		$this->setArgs(...array_slice(func_get_args(), 3));
	}

	public function setIdentifier(TIdentifier $identifier) : General
	{
		$this->identifier = $identifier;

		return $this;
	}

	public function getIdentifier() : TIdentifier
	{
		return $this->identifier;
	}

	public function getIdentifierWithArgs() : TIdentifier
	{
		return new TIdentifier(...array_merge($this->getIdentifier()->getParts(), $this->getArgs()));
	}

	public function setTimeout(Timeout $timeout) : General
	{
		$this->timeout = $timeout;

		return $this;
	}

	public function getTimeout() : Timeout
	{
		return $this->timeout;
	}

	public function setCallback(?callable $callback) : General
	{
		$this->callback = $callback;

		return $this;
	}

	public function getCallback() : ?callable
	{
		return $this->callback;
	}

	public function setArgs() : General
	{
		$this->args = func_get_args();

		return $this;
	}

	public function getArgs() : array
	{
		return $this->args;
	}

	public function disableMemory() : General
	{
		$this->enableApcu = false;
		$this->enableMemcached = false;
		$this->enableRedis = false;

		return $this;
	}

	public function getMemoryKey()
	{
		return $this->getIdentifierWithArgs()->getKey();
	}

	public function getFile() : \Katu\Files\File
	{
		return new \Katu\Files\File(\Katu\App::getTemporaryDir(), static::DIR_NAME, $this->getIdentifierWithArgs()->getPath('txt'));
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
		try {
			$appEnabled = (bool)\Katu\Config\Config::get('app', 'cache', 'memory', 'apc');
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			$appEnabled = true;
		}

		return $appEnabled && $this->enableApcu && static::isApcSupported();
	}

	public static function getMaxApcFileSize() : ?\Katu\Types\TFileSize
	{
		try {
			$ini = ini_get('apc.max_file_size');
			if (!$ini) {
				return null;
			}

			return new \Katu\Types\TFileSize(round(\Katu\Types\TFileSize::createFromINI($ini) * .8));
		} catch (\Throwable $e) {
			return null;
		}
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
		try {
			$appEnabled = (bool)\Katu\Config\Config::get('app', 'cache', 'memory', 'memcached');
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			$appEnabled = true;
		}

		return $appEnabled && $this->enableMemcached && static::isMemcachedSupported();
	}

	public static function getMemcahcedInstanceName()
	{
		return implode('.', [
			'appCache',
			\Katu\Config\Env::getShortHash(),
		]);
	}

	public static function loadMemcached()
	{
		if (!static::$memcached) {
			static::$memcached = new \Memcached;
			static::$memcached->addServer('localhost', 11211);
		}

		return static::$memcached;
	}

	public static function getMemcached()
	{
		return static::loadMemcached();
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
		try {
			$appEnabled = (bool)\Katu\Config\Config::get('app', 'cache', 'memory', 'redis');
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			$appEnabled = true;
		}

		return $appEnabled && $this->enableRedis && static::isRedisSupported();
	}

	public static function loadRedis()
	{
		if (!static::$redis) {
			static::$redis = new \Predis\Client;
		}

		return static::$redis;
	}

	public static function getRedis()
	{
		return static::loadRedis();
	}

	/****************************************************************************
	 * Result.
	 */
	public function getResult()
	{
		$memoryKey = $this->getMemoryKey();
		// Try Redis.
		if ($this->isRedisEnabled()) {
			$redis = static::getRedis();
			$res = $redis->get($memoryKey);
			if (!is_null($res)) {
				return unserialize($res);
			}
		}

		// Try Memcached.
		if ($this->isMemcachedEnabled()) {
			$memcached = static::getMemcached();
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
		if ($file->exists() && $this->getTimeout()->fits(\Katu\Tools\DateTime\DateTime::createFromTimestamp(filemtime($file)))) {
			return unserialize($file->get());
		}

		// Get result.
		$res = call_user_func_array($this->getCallback(), $this->getArgs());
		$serializedRes = serialize($res);

		// Try to save into Redis.
		if ($this->isRedisEnabled()) {
			// Add to Redis.
			$redis = static::getRedis();
			try {
				$args = [
					$memoryKey,
					serialize($res),
				];
				$seconds = $this->getTimeout()->getSeconds()->getValue();
				if ($seconds) {
					$args[] = 'EX';
					$args[] = $seconds;
				}
				$redis->set(...$args);
				return $res;
			} catch (\Throwable $e) {
				// (new \Katu\Tools\Logs\Logger('cache'))->error($e);
				$redis->del($memoryKey);
			}
		}

		// Try to save into Memcached.
		if ($this->isMemcachedEnabled()) {
			// Add to Memcached.
			$memcached = static::getMemcached();
			try {
				$seconds = $this->getTimeout()->getSeconds()->getValue();
				if (!$memcached->set($memoryKey, $res, $seconds ? time() + $seconds : 0)) {
					throw new \Exception;
				}
				return $res;
			} catch (\Throwable $e) {
				$memcached->delete($memoryKey);
			}
		}

		// Try to save into APC.
		$maxApcFileSize = static::getMaxApcFileSize();
		if ($this->isApcEnabled() && $maxApcFileSize && strlen($serializedRes) <= $maxApcFileSize->getInB()->getAmount()) {
			// Add to APC.
			try {
				if (!\apcu_store($memoryKey, $res, $this->getTimeout()->getSeconds()->getValue())) {
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

		try {
			static::getRedis()->del($memoryKey);
		} catch (\Throwable $e) {
			// Nevermind.
		}

		try {
			static::getMemcached()->delete($memoryKey);
		} catch (\Throwable $e) {
			// Nevermind.
		}

		// APC.
		try {
			\apcu_delete($memoryKey);
		} catch (\Throwable $e) {
			// Nevermind.
		}

		// File.
		try {
			$this->getFile()->delete();
		} catch (\Throwable $e) {
			// Nevermind.
		}

		return true;
	}

	public static function clearMemory()
	{
		try {
			if (static::isRedisSupported()) {
				static::getRedis()->flushall();
			}
		} catch (\Throwable $e) {
			// Nevermind.
		}

		try {
			if (static::isMemcachedSupported()) {
				static::getMemcached()->flush();
			}
		} catch (\Throwable $e) {
			// Nevermind.
		}

		try {
			if (static::isApcSupported()) {
				\apcu_clear_cache();
			}
		} catch (\Throwable $e) {
			// Nevermind.
		}

		return true;
	}

	public function exists() : bool
	{
		$memoryKey = $this->getMemoryKey();

		// Try Memcached.
		if ($this->isMemcachedEnabled()) {
			$memcached = static::getMemcached();
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
		if ($file->exists() && $this->getTimeout()->fits(\Katu\Tools\DateTime\DateTime::createFromTimestamp(filemtime($file)))) {
			return true;
		}

		return false;
	}

	/****************************************************************************
	 * Code sugar.
	 */
	public static function get(TIdentifier $identifier, Timeout $timeout, ?callable $callback = null)
	{
		$cache = new static($identifier, $timeout, $callback);
		$cache->setArgs(...array_slice(func_get_args(), 3));

		return $cache->getResult();
	}
}
