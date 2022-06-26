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

	public function write(string $name, $content)
	{
		return $this->getAdapter()->write(...func_get_args());
	}

	public function read(string $name)
	{
	}
}
