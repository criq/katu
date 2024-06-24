<?php

namespace Katu\Tools\System;

use Katu\Tools\Intl\Formatter;
use Katu\Tools\Intl\Locale;

abstract class DiskSpaceMonitor
{
	protected $mount;
	protected $limit;

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
}
