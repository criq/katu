<?php

namespace Katu\Tools\System\DiskSpaceMonitors;

use Katu\Tools\System\DiskSpaceMonitor;
use Katu\Types\TFileSize;

class AvailableLimit extends DiskSpaceMonitor
{
	public function __construct(string $mount, TFileSize $limit)
	{
		$this->setMount($mount);
		$this->setLimit($limit);
	}

	public function getIsPassed(): ?bool
	{
		try {
			return $this->getDiskSpace()->getAvailable()->getInB()->getAmount() >= $this->getLimit()->getInB()->getAmount();
		} catch (\Throwable $e) {
			return null;
		}
	}

	public function setLimit(TFileSize $limit): DiskSpaceMonitor
	{
		$this->limit = $limit;

		return $this;
	}

	public function getLimit(): TFileSize
	{
		return $this->limit;
	}
}
