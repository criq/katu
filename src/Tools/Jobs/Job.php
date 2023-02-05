<?php

namespace Katu\Tools\Jobs;

use App\Classes\Time;
use Katu\Cache\Pickle;
use Katu\Tools\Calendar\Timeout;
use Katu\Tools\Locks\Procedure;
use Katu\Types\TIdentifier;

abstract class Job
{
	abstract public function getCallback(): callable;
	abstract public function getLaunchInterval(): Timeout;
	abstract public function getTimeout(): Timeout;

	protected $args = [];

	public function __construct(array $args = [])
	{
		$this->setArgs($args);
	}

	public function setArgs(array $args = []): Job
	{
		$this->args = $args;

		return $this;
	}

	public function getArgs(): array
	{
		return $this->args;
	}

	public function getLastFinishedTimePickle(): Pickle
	{
		return new Pickle(new TIdentifier(static::class, __FUNCTION__));
	}

	public function setLastFinishedTime(?Time $time): Job
	{
		$this->getLastFinishedTimePickle()->set($time);

		return $this;
	}

	public function getLastFinishedTime(): ?Time
	{
		return $this->getLastFinishedTimePickle()->get() ?: null;
	}

	public function isExpired(): bool
	{
		if (is_null($this->getLastFinishedTime())) {
			return true;
		}

		if (!$this->getLastFinishedTime()->fitsInTimeout($this->getLaunchInterval())) {
			return true;
		}

		return false;
	}

	public function getIdentifier(): TIdentifier
	{
		return new TIdentifier(static::class);
	}

	public function getProcedure(): Procedure
	{
		return new Procedure($this->getIdentifier(), $this->getTimeout(), $this->getCallback());
	}

	public function run(): bool
	{
		try {
			$this->getProcedure()->run();
			$this->setLastFinishedTime(new Time);

			return true;
		} catch (\Throwable $e) {
			\App\App::getLogger(new TIdentifier(static::class, __FUNCTION__))->error($e);

			return false;
		}
	}
}
