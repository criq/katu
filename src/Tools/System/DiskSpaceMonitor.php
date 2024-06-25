<?php

namespace Katu\Tools\System;

use App\Classes\Calendar\Time;
use Katu\Cache\Pickle;
use Katu\Tools\Calendar\Timeout;
use Katu\Tools\Intl\Formatter;
use Katu\Tools\Intl\Locale;
use Katu\Types\TIdentifier;

abstract class DiskSpaceMonitor
{
	protected $cooldown;
	protected $limit;
	protected $mount;

	abstract public function getLimit();
	abstract public function getIsPassed(): ?bool;

	public function setMount(string $mount): DiskSpaceMonitor
	{
		$this->mount = $mount;

		return $this;
	}

	public function getMount(): string
	{
		return $this->mount;
	}

	public function getDiskSpace(): ?DiskSpace
	{
		return DiskSpaceCollection::createDefault()->getByMount($this->getMount());
	}

	public function getMessage(): string
	{
		$formatter = new Formatter(new Locale("cs_CZ"));

		return implode("\t", [
			"*{$this->getMount()}*",
			"*{$formatter->getLocalPercent($this->getDiskSpace()->getUsedPercentage())}* used",
			"*{$formatter->getLocalDecimalNumber($this->getDiskSpace()->getAvailable()->getReadable()->getAmount())} {$this->getDiskSpace()->getAvailable()->getReadable()->getUnit()}* available",
		]);
	}

	public function setCooldown(?Timeout $cooldown): DiskSpaceMonitor
	{
		$this->cooldown = $cooldown;

		return $this;
	}

	public function getCooldown(): Timeout
	{
		return $this->cooldown ?: $this->getDefaultCooldown();
	}

	public function getDefaultCooldown(): Timeout
	{
		return new Timeout("1 hour");
	}

	public function getTimeAlertedPickle(): Pickle
	{
		return new Pickle(new TIdentifier(__CLASS__, __FUNCTION__, $this->getMount(), $this->getLimit()));
	}

	public function setTimeAlerted(?Time $time): DiskSpaceMonitor
	{
		$this->getTimeAlertedPickle()->set($time);

		return $this;
	}

	public function getTimeAlerted(): ?Time
	{
		return $this->getTimeAlertedPickle()->get();
	}

	public function getIsWithinCooldown(): bool
	{
		return $this->getTimeAlerted() && $this->getTimeAlerted()->fitsInTimeout($this->getCooldown());
	}

	public function getIsAlerting(): bool
	{
		return $this->getIsPassed() === false && $this->getIsWithinCooldown() === false;
	}

	public function alert(?callable $callback = null): DiskSpaceMonitor
	{
		if ($this->getIsAlerting()) {
			if (is_callable($callback)) {
				call_user_func_array($callback, [$this]);
			}
			$this->setTimeAlerted(new Time);
		}

		return $this;
	}
}
