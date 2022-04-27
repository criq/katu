<?php

namespace Katu\Cache\Adapters;

use Katu\Tools\Calendar\Timeout;
use Katu\Types\TIdentifier;

class Redis implements \Katu\Cache\Adapter
{
	protected static $instance;

	public static function isSupported(): bool
	{
		try {
			$client = new \Predis\Client;
			$client->connect();

			return $client->isConnected();
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
			$res = $instance->exists($identifier);
			return (bool)$res;
		}

		return false;
	}

	public function get(TIdentifier $identifier, Timeout $timeout)
	{
		if (static::isSupported()) {
			$instance = static::getInstance();
			$res = $instance->get($identifier);
			if (!is_null($res)) {
				return unserialize($res);
			}
		}

		return null;
	}

	public function set(TIdentifier $identifier, Timeout $timeout, $value): bool
	{
		if (static::isSupported()) {
			$instance = static::getInstance();
			try {
				$args = [
					$identifier,
					serialize($value),
				];
				$seconds = abs($timeout->getSeconds()->getValue());
				if ($seconds) {
					$args[] = 'EX';
					$args[] = $seconds;
				}
				$instance->set(...$args);

				return true;
			} catch (\Throwable $e) {
				\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->error($e);

				$instance->del($identifier);
			}
		}

		return false;
	}

	public function delete(TIdentifier $identifier): bool
	{
		try {
			if (static::isSupported()) {
				static::getInstance()->del($identifier);

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
				static::getInstance()->flushall();

				return true;
			}
		} catch (\Throwable $e) {
			// Nevermind.
		}

		return false;
	}

	public static function getInstance()
	{
		if (!(static::$instance ?? null)) {
			static::$instance = new \Predis\Client;
		}

		return static::$instance;
	}
}
