<?php

namespace Katu\Storage;

use Katu\Types\TFileSize;
use Katu\Types\TIdentifier;

class Entity
{
	protected $contentType;
	protected $fileSize;
	protected $name;
	protected $storage;
	protected $uri;

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

	public function write($content): Entity
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

	public function setURI(string $uri): Entity
	{
		$this->uri = $uri;

		return $this;
	}

	public function getURI(): string
	{
		if (!$this->uri) {
			$this->uri = $this->getStorage()->getAdapter()->getURI($this);
		}

		return $this->uri;
	}

	public function setFileSize(?TFileSize $fileSize): Entity
	{
		$this->fileSize = $fileSize;

		return $this;
	}

	public function getFileSize(): TFileSize
	{
		if (!$this->fileSize) {
			$this->fileSize = $this->getStorage()->getAdapter()->getFileSize($this);
		}

		return $this->fileSize;
	}

	public function setContentType(?string $contentType): Entity
	{
		$this->contentType = $contentType;

		return $this;
	}

	public function getContentType(): string
	{
		if (!$this->contentType) {
			$this->contentType = $this->getStorage()->getAdapter()->getContentType($this);
		}

		return $this->contentType;
	}

	public function getLocalFile(): \Katu\Files\File
	{
		$extension = pathinfo($this->getURI())["extension"] ?? null;
		$identifier = new TIdentifier(__CLASS__, __FUNCTION__, sha1($this->getURI()));
		$file = new \Katu\Files\File(\App\App::getTemporaryDir(), $identifier->getPath($extension));
		$file->set($this->read());

		return $file;
	}
}
