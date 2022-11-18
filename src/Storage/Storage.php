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

	public function listEntities(): iterable
	{
		return $this->getAdapter()->listEntities($this);
	}

	public function getEntity(string $name): Entity
	{
		return new Entity($this, $name);
	}
}
