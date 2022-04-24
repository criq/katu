<?php

namespace Katu\Cache;

use Katu\Tools\Calendar\Timeout;
use Katu\Types\TIdentifier;

class Ledger
{
	const DIR_NAME = "ledgers";

	protected $identifier;

	public function __construct(TIdentifier $identifier)
	{
		$this->setIdentifier($identifier);
		$this->getFile()->touch();

		if (!$this->get()) {
			$this->set([]);
		}
	}

	public function setIdentifier(TIdentifier $identifier): Ledger
	{
		$this->identifier = $identifier;

		return $this;
	}

	public function getIdentifier(): TIdentifier
	{
		return $this->identifier;
	}

	public function getFile(): \Katu\Files\File
	{
		return new \Katu\Files\File(\Katu\App::getTemporaryDir(), static::DIR_NAME, $this->getIdentifier()->getPath("txt"));
	}

	public function populateKeys(array $keys): Ledger
	{
		$contents = $this->get();

		foreach ($keys as $key) {
			if (!isset($contents[$key])) {
				$contents[$key] = null;
			}
		}

		$this->set($contents);

		return $this;
	}

	public function get(): array
	{
		return unserialize($this->getFile()->get()) ?: [];
	}

	public function set($contents): Ledger
	{
		ksort($contents, \SORT_NATURAL);

		$this->getFile()->set(serialize($contents));

		return $this;
	}

	public function setKey($key, $value): Ledger
	{
		$contents = $this->get();
		$contents[$key] = $value;
		$this->set($contents);

		return $this;
	}

	public function setKeyLoaded($key): Ledger
	{
		$this->setKey($key, array_merge((array)$this->getKey($key), [
			"timeLoaded" => (new \Katu\Tools\Calendar\Time)->format("r"),
		]));

		return $this;
	}

	public function getKeyLoaded($key, Timeout $timeout, string $timeoutKey = "timeLoaded"): bool
	{
		try {
			return (new \Katu\Tools\Calendar\Time($this->getKey($key)[$timeoutKey]))->fitsInTimeout($timeout);
		} catch (\Throwable $e) {
			// Nevermind.
		}

		return false;
	}

	public function getKey($key)
	{
		return $this->get()[$key] ?? null;
	}

	public function getExpiredKeys(Timeout $timeout, string $timeoutKey = "timeLoaded"): array
	{
		$expired = [];
		foreach ($this->get() as $key => $value) {
			if (isset($value[$timeoutKey]) && (new \Katu\Tools\Calendar\Time($value[$timeoutKey]))->fitsInTimeout($timeout)) {
				// Not expired.
			} elseif (isset($value[$timeoutKey])) {
				$expired[$key] = "B" . (new \Katu\Tools\Calendar\Time($value[$timeoutKey]))->getTimestamp();
			} else {
				$expired[$key] = "A" . $key;
			}
		}

		natsort($expired);

		return array_keys($expired);
	}

	public function removeKey($key): Ledger
	{
		$contents = $this->get();
		unset($contents[$key]);
		$this->set($contents);

		return $this;
	}

	public function count(): int
	{
		return count($this->get());
	}

	public function getKeys(): array
	{
		return array_keys($this->get());
	}

	public function getMaxKey()
	{
		try {
			return max($this->getKeys());
		} catch (\Throwable $e) {
			return null;
		}
	}
}
