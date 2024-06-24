<?php

namespace Katu\Tools\System;

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
}
