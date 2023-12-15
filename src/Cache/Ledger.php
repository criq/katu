<?php

namespace Katu\Cache;

use Katu\Tools\Calendar\Time;
use Katu\Tools\Calendar\Timeout;
use Katu\Types\TIdentifier;

class Ledger
{
	const DIR_NAME = "ledgers";

	protected $identifier;
	protected $ledgerKeys;

	public function __construct(TIdentifier $identifier)
	{
		$this->setIdentifier($identifier);
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

	public function populate(array $keys): Ledger
	{
		$this->getLedgerKeys()->populate($keys);
		$this->persist();

		return $this;
	}

	public function getKey(string $key): ?LedgerKey
	{
		return array_values($this->getLedgerKeys()->filterByKey($key)->getArrayCopy())[0] ?? null;
	}

	public function getKeys(): array
	{
		return $this->getLedgerKeys()->getKeys();
	}

	public function getMaxKey(): ?string
	{
		try {
			return max($this->getKeys());
		} catch (\Throwable $e) {
			// Nevermind.
		}

		return null;
	}

	public function setKeyTime(string $key, ?Time $time): Ledger
	{
		try {
			$this->getLedgerKeys()->getLedgerKey($key)->setTime($time);
			$this->persist();
		} catch (\Throwable $e) {
			// Nevermind.
		}

		return $this;
	}

	public function removeKey(string $key): Ledger
	{
		$this->getLedgerKeys()->removeLedgerKey($key);
		$this->persist();

		return $this;
	}

	public function getExpiredKeys(Timeout $timeout): array
	{
		return $this->getLedgerKeys()->filterExpired($timeout)->getKeys();
	}

	public function getNotExpiredKeys(Timeout $timeout): array
	{
		return $this->getLedgerKeys()->filterNotExpired($timeout)->getKeys();
	}

	protected function getFile(): \Katu\Files\File
	{
		return new \Katu\Files\File(\App\App::getTemporaryDir(), static::DIR_NAME, $this->getIdentifier()->getPath("txt"));
	}

	protected function getLedgerKeys(): LedgerKeyCollection
	{
		if (is_null($this->ledgerKeys)) {
			try {
				$unserialized = unserialize($this->getFile()->get());
				if ($unserialized instanceof LedgerKeyCollection) {
					$this->ledgerKeys = $unserialized;
				}
			} catch (\Throwable $e) {
				// Nevermind.
			}
		}

		if (is_null($this->ledgerKeys)) {
			$this->ledgerKeys = new LedgerKeyCollection;
		}

		return $this->ledgerKeys;
	}

	protected function persist(): Ledger
	{
		$this->getFile()->set(serialize($this->getLedgerKeys()));

		return $this;
	}
}
