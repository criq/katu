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

	public function sortByTime(): LedgerKeyCollection
	{
		$array = $this->getArrayCopy();

		uasort($array, function (LedgerKey $a, LedgerKey $b) {
			return $a->getTime() > $b->getTime() ? 1 : -1;
		});

		return new static($array);
	}

	public function filterExpired(Timeout $timeout): LedgerKeyCollection
	{
		return (new LedgerKeyCollection(array_filter($this->getArrayCopy(), function (LedgerKey $key) use ($timeout) {
			return $key->getIsExpired($timeout);
		})));
	}

	public function filterNotExpired(Timeout $timeout): LedgerKeyCollection
	{
		return (new LedgerKeyCollection(array_filter($this->getArrayCopy(), function (LedgerKey $key) use ($timeout) {
			return !$key->getIsExpired($timeout);
		})));
	}

	public function filterByKey(string $string): LedgerKeyCollection
	{
		return (new LedgerKeyCollection(array_filter($this->getArrayCopy(), function (LedgerKey $key) use ($string) {
			return $key->getKey() == $string;
		})));
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
