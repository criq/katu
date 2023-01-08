<?php

namespace Katu\Cache;

use Katu\Tools\Calendar\Timeout;

class LedgerKeyCollection extends \ArrayObject
{
	public function populate(array $keys): LedgerKeyCollection
	{
		foreach ($keys as $key) {
			$this[(string)$key] = ($this[$key] ?? null) ? $this[$key] : new LedgerKey($key);
		}

		return $this;
	}

	public function getKeys(): array
	{
		return array_map(function ($key) {
			return (string)$key;
		}, array_keys($this->getArrayCopy()));
	}

	public function filterExpired(Timeout $timeout): LedgerKeyCollection
	{
		return new LedgerKeyCollection(array_filter($this->getArrayCopy(), function (LedgerKey $key) use ($timeout) {
			return is_null($key->getTime()) || !$key->getTime()->fitsInTimeout($timeout);
		}));
	}

	public function getLedgerKey(string $key): ?LedgerKey
	{
		return $this[$key] ?? null;
	}

	public function removeLedgerKey(string $key): LedgerKeyCollection
	{
		unset($this[$key]);

		return $this;
	}
}
