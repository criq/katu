<?php

namespace Katu\Storage;

use Katu\Types\TFileSize;
use Katu\Types\TIdentifier;

class Entity
{
	protected $name;
	protected $storage;

	public function __construct(Storage $storage, string $name)
	{
		$this->setStorage($storage);
		$this->setName($name);
	}

	public function setName(string $name): Entity
	{
		$this->name = $name;

		return $this;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setStorage(Storage $storage): Entity
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

	public function getLocalCopy(): \Katu\Files\File
	{
		$extension = pathinfo($this->getURI())["extension"] ?? null;
		$identifier = new TIdentifier(__CLASS__, __FUNCTION__, sha1($this->getURI()));
		$file = new \Katu\Files\File(\App\App::getTemporaryDir(), $identifier->getPath($extension));
		$file->set($this->read());

		return $file;
	}
}
