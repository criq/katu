<?php

namespace Katu\Tools\Locks;

class Lock {

	const DIR_NAME = 'locks';

	private $timeout;
	private $name;
	private $callback;
	private $args = [];
	private $useLock = true;
	private $excludedPlatforms = [];

	public function __construct(int $timeout, $name = null, callable $callback = null) {
		$this->timeout = $timeout;
		$this->name = $name;
		$this->callback = $callback;

		if (!$this->name) {
			$origin = debug_backtrace()[1];
			$this->name = [$origin['class'], $origin['function']];
		}
	}

	public function setName($name) {
		$this->name = $name;

		return $this;
	}

	public function setTimeout(int $timeout) {
		$this->timeout = $timeout;

		return $this;
	}

	public function setCallback(callable $callback) {
		$this->callback = $callback;

		return $this;
	}

	public function setArgs(array $args) {
		$this->args = $args;

		return $this;
	}

	public function setUseLock($useLock) {
		$this->useLock = $useLock;

		return $this;
	}

	public function getUseLock() {
		return $this->useLock && !in_array(\Katu\Config\Env::getPlatform(), $this->excludedPlatforms);
	}

	public function excludePlatform($platform) {
		$this->excludedPlatforms[] = $platform;

		return $this;
	}

	public function getFile() {
		return \Katu\Files\File::createFromName(\Katu\App::getTmpDir(), static::DIR_NAME, $this->name);
	}

	public function isLocked() {
		$file = $this->getFile();
		if (!$file->exists()) {
			return false;
		}

		if ($file->getDateTimeModified()->isInTimeout($this->timeout)) {
			return true;
		}

		return false;
	}

	public function lock() {
		if ($this->isLocked()) {
			throw new \Katu\Exceptions\LockException;
		}

		$this->getFile()->touch();

		return true;
	}

	public function unlock() {
		$file = $this->getFile();
		if ($file->exists()) {
			$file->delete();
		}

		return true;
	}

	public function run() {
		if ($this->getUseLock()) {
			$this->lock();
		}

		@set_time_limit($this->timeout);
		$callback = $this->callback;
		$res = $callback(...$this->args);

		if ($this->getUseLock()) {
			$this->unlock();
		}

		return $res;
	}

}
