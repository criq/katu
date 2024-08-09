<?php

namespace Katu\Tools\Locks;

use Katu\Tools\Calendar\Timeout;
use Katu\Types\TIdentifier;

class Procedure
{
	protected $callback;
	protected $identifier;
	protected $isLockChecked = true;
	protected $timeout;

	public function __construct(TIdentifier $identifier, Timeout $timeout, callable $callback)
	{
		$this->setIdentifier($identifier);
		$this->setTimeout($timeout);
		$this->setCallback($callback);
	}

	public function setIdentifier(TIdentifier $value): Procedure
	{
		$this->identifier = $value;

		return $this;
	}

	public function getIdentifier(): TIdentifier
	{
		return $this->identifier;
	}

	public function setTimeout(Timeout $value): Procedure
	{
		$this->timeout = $value;

		return $this;
	}

	public function getTimeout(): Timeout
	{
		return $this->timeout;
	}

	public function setCallback(callable $value): Procedure
	{
		$this->callback = $value;

		return $this;
	}

	public function getCallback(): callable
	{
		return $this->callback;
	}

	public function getLock(): Lock
	{
		return new Lock($this->getIdentifier(), $this->getTimeout());
	}

	public function setIsLockChecked(bool $isLockChecked): Procedure
	{
		$this->isLockChecked = $isLockChecked;

		return $this;
	}

	public function getIsLockChecked(): bool
	{
		return $this->isLockChecked;
	}

	public function getIsExecutable(): bool
	{
		if (!$this->getIsLockChecked()) {
			return true;
		}

		if ($this->getIsLockChecked() && !$this->getLock()->getIsLocked()) {
			return true;
		}

		return false;
	}

	public function run()
	{
		$lock = $this->getLock();

		try {
			if ($this->getIsExecutable()) {
				if (!$this->getIsLockChecked()) {
					if ($lock->getIsLocked()) {
						$lock->unlock();
					}
				}
				$lock->lock();

				@set_time_limit((string)$this->getTimeout()->getSeconds());
				$res = call_user_func($this->getCallback());
			}
		} catch (\Katu\Exceptions\LockException $e) {
			// Nevermind.
		} catch (\Throwable $e) {
			\App\App::getLogger($this->getIdentifier())->error($e);
		} finally {
			$lock->unlock();
		}

		return $res ?? null;
	}
}
