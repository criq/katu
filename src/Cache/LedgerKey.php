<?php

namespace Katu\Cache;

use Katu\Tools\Calendar\Time;
use Katu\Tools\Calendar\Timeout;

class LedgerKey
{
	protected $key;
	protected $time;

	public function __construct(string $key)
	{
		$this->setKey($key);
	}

	public function setKey(string $key): LedgerKey
	{
		$this->key = $key;

		return $this;
	}

	public function getKey(): string
	{
		return $this->key;
	}

	public function setTime(?Time $time): LedgerKey
	{
		$this->time = $time;

		return $this;
	}

	public function getTime(): ?Time
	{
		return $this->time;
	}

	public function getIsExpired(Timeout $timeout): bool
	{
		return is_null($this->getTime()) || !$this->getTime()->fitsInTimeout($timeout);
	}
}
