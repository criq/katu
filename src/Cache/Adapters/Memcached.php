<?php

namespace Katu\Cache\Adapters;

use Katu\Tools\Calendar\Timeout;
use Katu\Types\TIdentifier;

class Memcached implements \Katu\Cache\Adapter
{
	protected static $instance;

	public static function isSupported(): bool
	{
		try {
			return class_exists('Memcached');
		} catch (\Throwable $e) {
			return false;
		}
	}

	public static function isMemory(): bool
	{
		return true;
	}

	public function exists(TIdentifier $identifier, Timeout $timeout): bool
	{
		if (static::isSupported()) {
			$instance = static::getInstance();
			$instance->get($this->getInstance());
			if ($instance->getResultCode() === \Memcached::RES_SUCCESS) {
				return true;
			}
		}

		return false;
	}

	public function get(TIdentifier $identifier, Timeout $timeout)
	{
		if (static::isSupported()) {
			$instance = static::getInstance();
			$res = $instance->get($identifier);
			if ($instance->getResultCode() === \Memcached::RES_SUCCESS) {
				return $res;
			}
		}

		return null;
	}

	public function set(TIdentifier $identifier, Timeout $timeout, $value): bool
	{
		if (static::isSupported()) {
			$instance = static::getInstance();
			try {
				$seconds = abs($timeout->getSeconds()->getValue());
				if (!$instance->set($identifier, $value, $seconds ? time() + $seconds : 0)) {
					throw new \Exception;
				}

				return true;
			} catch (\Throwable $e) {
				\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->error($e);

				$instance->delete($identifier);
			}
		}

		return false;
	}

	public function delete(TIdentifier $identifier): bool
	{
		try {
			if (static::isSupported()) {
				static::getInstance()->delete($identifier);

				return true;
			}
		} catch (\Throwable $e) {
			// Nevermind.
		}

		return false;
	}

	public function flush(): bool
	{
		try {
			if (static::isSupported()) {
				static::getInstance()->flush();

				return true;
			}
		} catch (\Throwable $e) {
			// Nevermind.
		}

		return false;
	}

	public static function getInstance()
	{
		if (!static::$instance) {
			static::$instance = new \Memcached;
			static::$instance->addServer('localhost', 11211);
		}

		return static::$instance;
	}
}
