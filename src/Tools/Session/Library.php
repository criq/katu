<?php

namespace Katu\Tools\Session;

abstract class Library extends \ArrayObject
{
	const KEY = "";

	protected $key;

	public function __construct(string $key)
	{
		$this->setKey($key);
	}

	public function setKey(string $key): Library
	{
		$this->key = $key;

		return $this;
	}

	public function getKey(): string
	{
		return $this->key;
	}
}
