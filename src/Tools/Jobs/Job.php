<?php

namespace Katu\Tools\Jobs;

use Katu\Cache\Pickle;
use Katu\Tools\Calendar\Time;
use Katu\Tools\Calendar\Timeout;
use Katu\Tools\Locks\Procedure;
use Katu\Types\TIdentifier;

abstract class Job
{
	abstract public function getCallback(): callable;

	const INTERVAL = "1 day";
	const MAX_LOAD_AVERAGE = 1.5;
	const TIMEOUT = "1 hour";

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

	public function getInterval(): Timeout
	{
		return new Timeout(static::INTERVAL);
	}

	public function getTimeout(): Timeout
	{
		return new Timeout(static::TIMEOUT);
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

	public function getMaxLoadAverage(): ?float
	{
		return static::MAX_LOAD_AVERAGE;
	}

	public function getIdentifier(): TIdentifier
	{
		return new TIdentifier(static::class);
	}

	public function getProcedure(): Procedure
	{
		return new Procedure($this->getIdentifier(), $this->getTimeout(), $this->getCallback());
	}

	public function getSchedules(): ScheduleCollection
	{
		return new ScheduleCollection([
			new Schedule,
		]);
	}

	public function isScheduled(Time $time): bool
	{
		foreach ($this->getSchedules() as $schedule) {
			if (preg_match("/^{$schedule->getRegexp()}$/", $time->format("m d H i"))) {
				return true;
			}
		}

		return false;
	}

	public function run(): bool
	{
		// Check max load average.
		if ($this->getMaxLoadAverage() && \Katu\Tools\System\System::getLoadAveragePerCpu()[0] >= $this->getMaxLoadAverage()) {
			return false;
		}

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
