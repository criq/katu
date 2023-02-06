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
	abstract public function getInterval(): Timeout;
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

	public function getTimeStartedPickle(): Pickle
	{
		return new Pickle(new TIdentifier(static::class, __FUNCTION__));
	}

	public function setTimeStarted(?Time $time): Job
	{
		$this->getTimeStartedPickle()->set($time);

		return $this;
	}

	public function getTimeStarted(): ?Time
	{
		return $this->getTimeStartedPickle()->get() ?: null;
	}

	public function getTimeFinishedPickle(): Pickle
	{
		return new Pickle(new TIdentifier(static::class, __FUNCTION__));
	}

	public function setTimeFinished(?Time $time): Job
	{
		$this->getTimeFinishedPickle()->set($time);

		return $this;
	}

	public function getTimeFinished(): ?Time
	{
		return $this->getTimeFinishedPickle()->get() ?: null;
	}

	public function isExpired(): bool
	{
		if (is_null($this->getTimeFinished())) {
			return true;
		}

		if (!$this->getTimeFinished()->fitsInTimeout($this->getInterval())) {
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
			$this->setTimeStarted(new Time);
			$this->getProcedure()->run();
			$this->setTimeFinished(new Time);

			return true;
		} catch (\Throwable $e) {
			\App\App::getLogger(new TIdentifier(static::class, __FUNCTION__))->error($e);

			return false;
		}
	}
}
