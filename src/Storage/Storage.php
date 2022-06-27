<?php

namespace Katu\Storage;

class Storage
{
	protected $adapter;

	public function __construct(AdapterInterface $adapter)
	{
		$this->setAdapter($adapter);
	}

	public function setAdapter(AdapterInterface $adapter): Storage
	{
		$this->adapter = $adapter;

		return $this;
	}

	public function getAdapter(): AdapterInterface
	{
		return $this->adapter;
	}

	public function getStorageItem(string $name): StorageItem
	{
		return new StorageItem($this, $name);
	}
}
