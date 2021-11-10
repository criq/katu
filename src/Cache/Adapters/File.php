<?php

namespace Katu\Cache\Adapters;

use Katu\Tools\DateTime\Timeout;
use Katu\Types\TIdentifier;

class File implements \Katu\Cache\Adapter
{
	const DIR_NAME = 'cache';

	public static function isSupported(): bool
	{
		return true;
	}

	public static function isMemory(): bool
	{
		return false;
	}

	public function exists(TIdentifier $identifier, Timeout $timeout): bool
	{
		if (static::isSupported()) {
			$file = $this->getFile($identifier);
			if ($file->exists() && $timeout->fits(\Katu\Tools\DateTime\DateTime::createFromTimestamp(filemtime($file)))) {
				return true;
			}
		}

		return false;
	}

	public function get(TIdentifier $identifier, Timeout $timeout)
	{
		if (static::isSupported()) {
			$file = $this->getFile($identifier);
			if ($file->exists() && $timeout->fits(\Katu\Tools\DateTime\DateTime::createFromTimestamp(filemtime($file)))) {
				return unserialize($file->get());
			}
		}

		return null;
	}

	public function set(TIdentifier $identifier, Timeout $timeout, $value): bool
	{
		if (static::isSupported()) {
			try {
				$this->getFile($identifier)->set(serialize($value));

				return true;
			} catch (\Throwable $e) {
				(new \Katu\Tools\Logs\Logger(new TIdentifier(__CLASS__, __FUNCTION__)))->error($e);
			}
		}

		return false;
	}

	public function delete(TIdentifier $identifier): bool
	{
		try {
			if (static::isSupported()) {
				$this->getFile($identifier)->delete();

				return true;
			}
		} catch (\Throwable $e) {
			// Nevermind.
		}

		return false;
	}

	public function flush(): bool
	{
		return false;
	}

	public function getFile(TIdentifier $identifier): \Katu\Files\File
	{
		return new \Katu\Files\File(\Katu\App::getTemporaryDir(), static::DIR_NAME, $identifier->getPath('txt'));
	}
}
