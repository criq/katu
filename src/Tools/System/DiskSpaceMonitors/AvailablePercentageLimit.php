<?php

namespace Katu\Tools\System\DiskSpaceMonitors;

use Katu\Tools\System\DiskSpaceMonitor;

class AvailablePercentageLimit extends DiskSpaceMonitor
{
	public function __construct(string $mount, float $limit)
	{
		$this->setMount($mount);
		$this->setLimit($limit);
	}

	public function getIsPassed(): ?bool
	{
		try {
			return $this->getDiskSpace()->getAvailablePercentage() > $this->getLimit();
		} catch (\Throwable $e) {
			return null;
		}
	}

	public function setLimit(float $limit): DiskSpaceMonitor
	{
		$this->limit = $limit;

		return $this;
	}

	public function getLimit(): float
	{
		return $this->limit;
	}
}
