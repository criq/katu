<?php

namespace Katu\Storage;

class Resource
{
	protected $uri;
	protected $adapter;

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

	public function setAdapter(AdapterInterface $adapter): Resource
	{
		$this->adapter = $adapter;

		return $this;
	}

	public function getAdapter(): ?AdapterInterface
	{
		return $this->adapter;
	}
}
