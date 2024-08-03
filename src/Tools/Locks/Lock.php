<?php

namespace Katu\Tools\Locks;

use Katu\Tools\Calendar\Timeout;
use Katu\Types\TIdentifier;

class Lock
{
	const DIR_NAME = "locks";

	private $identifier;
	private $timeout;

	public function __construct(TIdentifier $identifier, Timeout $timeout)
	{
		$this->setIdentifier($identifier);
		$this->setTimeout($timeout);
	}

	public function setIdentifier(TIdentifier $identifier): Lock
	{
		$this->identifier = $identifier;

		return $this;
	}

	public function getIdentifier(): TIdentifier
	{
		return $this->identifier;
	}

	public function setTimeout(Timeout $timeout): Lock
	{
		$this->timeout = $timeout;

		return $this;
	}

	public function getTimeout(): Timeout
	{
		return $this->timeout;
	}

	public function getFile(): \Katu\Files\File
	{
		return new \Katu\Files\File(\App\App::getTemporaryDir(), static::DIR_NAME, $this->getIdentifier()->getPath("lock"));
	}

	public function getIsLocked(): bool
	{
		$file = $this->getFile();
		if (!$file->exists()) {
			return false;
		}

		if ($file->getModifiedTime()->getTimestamp() >= $this->getTimeout()->getTime()->getTimestamp()) {
			return true;
		}

		return false;
	}

	public function lock(): Lock
	{
		if ($this->getIsLocked()) {
			throw new \Katu\Exceptions\LockException;
		}

		$this->getFile()->touch();

		return $this;
	}

	public function unlock(): Lock
	{
		$file = $this->getFile();
		if ($file->exists()) {
			$file->delete();
		}

		return $this;
	}
}
