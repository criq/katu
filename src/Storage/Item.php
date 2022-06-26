<?php

namespace Katu\Storage;

class Item
{
	protected $name;
	protected $storage;

	public function __construct(Storage $storage, string $name)
	{
		$this->setStorage($storage);
		$this->setName($name);
	}

	public function setName(string $name): Item
	{
		$this->name = $name;

		return $this;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setStorage(Storage $storage): Item
	{
		$this->storage = $storage;

		return $this;
	}

	public function getStorage(): Storage
	{
		return $this->storage;
	}

	public function write($content): Item
	{
		$this->getStorage()->getAdapter()->write($this, $content);

		return $this;
	}

	public function read()
	{
	}

	public function getURI(): string
	{
		return $this->getStorage()->getAdapter()->getURI($this);
	}

	public function getSize(): int
	{
		return $this->getStorage()->getAdapter()->getSize($this);
	}

	public function getContentType()
	{
		return $this->getStorage()->getAdapter()->getContentType($this);
	}
}
