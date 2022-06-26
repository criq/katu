<?php

namespace Katu\Storage;

class Resource
{
	protected $storage;
	protected $uri;

	public function __construct(string $uri)
	{
		$this->setURI($uri);
	}

	public function setURI(string $uri): Resource
	{
		$this->uri = $uri;

		return $this;
	}

	public function getURI(): string
	{
		return $this->uri;
	}

	public function setStorage(Storage $storage): Resource
	{
		$this->storage = $storage;

		return $this;
	}

	public function getStorage(): ?Storage
	{
		return $this->storage;
	}

	public function getSize()
	{
		return $this->getStorage()->getAdapter()->getSize($this->getURI());
	}

	public function getMime()
	{
	}
}
