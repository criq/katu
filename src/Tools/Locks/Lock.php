<?php

namespace Katu\Tools\Locks;

class Lock
{
	const DIR_NAME = 'locks';

	private $args = [];
	private $callback;
	private $excludedPlatforms = [];
	private $name;
	private $timeout;
	private $useLock = true;

	public function __construct(\Katu\Tools\DateTime\Timeout $timeout, mixed $name = null, callable $callback = null)
	{
		$this->setTimeout($timeout);
		$this->setName($name);
		$this->setCallback($callback);

		if (!$this->getName()) {
			$origin = debug_backtrace()[1];
			$this->setName([$origin['class'], $origin['function']]);
		}
	}

	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setTimeout(\Katu\Tools\DateTime\Timeout $timeout)
	{
		$this->timeout = $timeout;

		return $this;
	}

	public function getTimeout() : ?\Katu\Tools\DateTime\Timeout
	{
		return $this->timeout;
	}

	public function setCallback(callable $callback)
	{
		$this->callback = $callback;

		return $this;
	}

	public function getCallback()
	{
		return $this->callback;
	}

	public function setArgs(array $args)
	{
		$this->args = $args;

		return $this;
	}

	public function setUseLock($useLock)
	{
		$this->useLock = $useLock;

		return $this;
	}

	public function getUseLock()
	{
		return $this->useLock && !in_array(\Katu\Config\Env::getPlatform(), $this->excludedPlatforms);
	}

	public function excludePlatform($platform)
	{
		$this->excludedPlatforms[] = $platform;

		return $this;
	}

	public function getFile()
	{
		return new \Katu\Files\File(\Katu\App::getTemporaryDir(), static::DIR_NAME, \Katu\Files\File::generatePath($this->name, 'lock'));
	}

	public function isLocked()
	{
		$file = $this->getFile();
		if (!$file->exists()) {
			return false;
		}

		if ($file->getDateTimeModified() >= $this->getTimeout()->getDateTime()) {
			return true;
		}

		return false;
	}

	public function lock()
	{
		if ($this->isLocked()) {
			throw new \Katu\Exceptions\LockException;
		}

		$this->getFile()->touch();

		return true;
	}

	public function unlock()
	{
		$file = $this->getFile();
		if ($file->exists()) {
			$file->delete();
		}

		return true;
	}

	public function run()
	{
		if ($this->getUseLock()) {
			$this->lock();
		}

		@set_time_limit($this->getTimeout()->getSeconds());
		$callback = $this->getCallback();
		$res = $callback(...$this->args);

		if ($this->getUseLock()) {
			$this->unlock();
		}

		return $res;
	}
}
