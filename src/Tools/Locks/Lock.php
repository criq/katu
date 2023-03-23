<?php

namespace Katu\Tools\Locks;

use Katu\Tools\Calendar\Timeout;
use Katu\Types\TIdentifier;

class Lock
{
	const DIR_NAME = "locks";

	private $args = [];
	private $callback;
	private $excludedPlatforms = [];
	private $identifier;
	private $timeout;
	private $useLock = true;

	public function __construct(TIdentifier $identifier, Timeout $timeout, ?callable $callback = null)
	{
		$this->setIdentifier($identifier);
		$this->setTimeout($timeout);
		$this->setCallback($callback);
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

	public function setCallback(?callable $callback): Lock
	{
		$this->callback = $callback;

		return $this;
	}

	public function getCallback(): ?callable
	{
		return $this->callback;
	}

	public function setArgs(): Lock
	{
		$this->args = func_get_args();

		return $this;
	}

	public function getArgs(): array
	{
		return $this->args;
	}

	public function setUseLock(bool $useLock): Lock
	{
		$this->useLock = $useLock;

		return $this;
	}

	public function getUseLock(): bool
	{
		return (bool)($this->useLock && !in_array(\Katu\Config\Env::getPlatform(), $this->excludedPlatforms));
	}

	public function excludePlatform(string $platform): Lock
	{
		$this->excludedPlatforms[] = $platform;

		return $this;
	}

	public function getFile(): \Katu\Files\File
	{
		return new \Katu\Files\File(\App\App::getTemporaryDir(), static::DIR_NAME, $this->getIdentifier()->getPath("lock"));
	}

	public function isLocked(): bool
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
		if ($this->isLocked()) {
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

	public function run()
	{
		if ($this->getUseLock()) {
			$this->lock();
		}

		@set_time_limit((string)$this->getTimeout()->getSeconds());
		$res = call_user_func_array($this->getCallback(), $this->getArgs());

		if ($this->getUseLock()) {
			$this->unlock();
		}

		return $res;
	}
}
