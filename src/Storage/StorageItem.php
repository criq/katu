<?php

namespace Katu\Storage;

use Katu\Types\TFileSize;

class StorageItem
{
	protected $name;
	protected $storage;

	public function __construct(Storage $storage, string $name)
	{
		$this->setStorage($storage);
		$this->setName($name);
	}

	public function setName(string $name): StorageItem
	{
		$this->name = $name;

		return $this;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setStorage(Storage $storage): StorageItem
	{
		$this->storage = $storage;

		return $this;
	}

	public function getStorage(): Storage
	{
		return $this->storage;
	}

	public function write($content): StorageItem
	{
		$this->getStorage()->getAdapter()->write($this, $content);

		return $this;
	}

	public function read()
	{
		return $this->getStorage()->getAdapter()->read($this);
	}

	public function delete(): bool
	{
		return $this->getStorage()->getAdapter()->delete($this);
	}

	public function getURI(): string
	{
		return $this->getStorage()->getAdapter()->getURI($this);
	}

	public function getFileSize(): TFileSize
	{
		return $this->getStorage()->getAdapter()->getFileSize($this);
	}

	public function getContentType(): string
	{
		return $this->getStorage()->getAdapter()->getContentType($this);
	}
}
