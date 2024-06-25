<?php

namespace Katu\Tools\System;

use Katu\Types\TFileSize;

class DiskSpace
{
	protected $capacity;
	protected $filesystem;
	protected $mount;
	protected $used;

	public function __construct(string $filesystem, string $mount, TFileSize $capacity, TFileSize $used)
	{
		$this->setCapacity($capacity);
		$this->setFilesystem($filesystem);
		$this->setMount($mount);
		$this->setUsed($used);
	}

	public function setFilesystem(string $filesystem): DiskSpace
	{
		$this->filesystem = $filesystem;

		return $this;
	}

	public function setMount(string $mount): DiskSpace
	{
		$this->mount = $mount;

		return $this;
	}

	public function getMount(): string
	{
		return $this->mount;
	}

	public function setCapacity(TFileSize $capacity): DiskSpace
	{
		$this->capacity = $capacity;

		return $this;
	}

	public function getCapacity(): TFileSize
	{
		return $this->capacity;
	}

	public function setUsed(TFileSize $used): DiskSpace
	{
		$this->used = $used;

		return $this;
	}

	public function getUsed(): TFileSize
	{
		return $this->used;
	}

	public function getUsedPercentage(): float
	{
		return $this->getUsed()->getInB()->getAmount() / $this->getCapacity()->getInB()->getAmount();
	}

	public function getAvailable(): TFileSize
	{
		return new TFileSize($this->getCapacity()->getInB()->getAmount() - $this->getUsed()->getInB()->getAmount());
	}

	public function getAvailablePercentage(): float
	{
		return $this->getAvailable()->getInB()->getAmount() / $this->getCapacity()->getInB()->getAmount();
	}
}
