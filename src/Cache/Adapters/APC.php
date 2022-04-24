<?php

namespace Katu\Cache\Adapters;

use Katu\Tools\Calendar\Timeout;
use Katu\Types\TIdentifier;

class APC implements \Katu\Cache\Adapter
{
	public static function isSupported(): bool
	{
		try {
			return function_exists("apcu_exists");
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
			if (\apcu_exists($identifier)) {
				$success = null;
				\apcu_fetch($identifier, $success);
				if ($success) {
					return true;
				}
			}
		}

		return false;
	}

	public function get(TIdentifier $identifier, Timeout $timeout)
	{
		if (static::isSupported()) {
			$success = null;
			$res = \apcu_fetch($identifier, $success);
			if ($success) {
				return $res;
			}
		}

		return null;
	}

	public function set(TIdentifier $identifier, Timeout $timeout, $value): bool
	{
		if (static::isSupported()) {
			$maxApcFileSize = static::getMaxFileSize();
			if ($maxApcFileSize && strlen($value) <= $maxApcFileSize->getInB()->getAmount()) {
				try {
					if (!\apcu_store($identifier, $value, abs($timeout->getSeconds()->getValue()))) {
						throw new \Exception;
					}

					return true;
				} catch (\Throwable $e) {
					(new \Katu\Tools\Logs\Logger(new TIdentifier(__CLASS__, __FUNCTION__)))->error($e);

					\apcu_delete($identifier);
				}
			}
		}

		return false;
	}

	public function delete(TIdentifier $identifier): bool
	{
		try {
			if (static::isSupported()) {
				\apcu_delete($identifier);

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
				\apcu_clear_cache();

				return true;
			}
		} catch (\Throwable $e) {
			// Nevermind.
		}

		return false;
	}

	public static function getMaxFileSize(): ?\Katu\Types\TFileSize
	{
		try {
			$ini = ini_get("apc.max_file_size");
			if (!$ini) {
				return null;
			}

			return new \Katu\Types\TFileSize(round(\Katu\Types\TFileSize::createFromShorthand($ini) * .8));
		} catch (\Throwable $e) {
			return null;
		}
	}
}
