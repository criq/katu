<?php

namespace Katu\Tools\Jobs;

use Katu\Tools\Calendar\Seconds;
use Katu\Tools\Calendar\Time;
use Katu\Tools\Calendar\Timeout;
use Katu\Tools\Locks\Procedure;
use Katu\Tools\Profiler\Stopwatch;
use Katu\Types\TIdentifier;

class JobCollection extends \ArrayObject
{
	protected $lockTimeout;
	protected $maxRunningSeconds;

	public function getIdentifier(): TIdentifier
	{
		return new TIdentifier(static::class);
	}

	public function setLockTimeout(Timeout $lockTimeout): JobCollection
	{
		$this->lockTimeout = $lockTimeout;

		return $this;
	}

	public function getLockTimeout(): Timeout
	{
		return $this->lockTimeout ?: new Timeout("1 hour");
	}

	public function setMaxRunningSeconds(Seconds $maxRunningSeconds): JobCollection
	{
		$this->maxRunningSeconds = $maxRunningSeconds;

		return $this;
	}

	public function getMaxRunningSeconds(): Seconds
	{
		return $this->maxRunningSeconds ?: Seconds::createFromString("10 minutes");
	}

	public function filterExpired(): JobCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Job $job) {
			return $job->isExpired();
		})));
	}

	public function filterScheduled(Time $time): JobCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Job $job) use ($time) {
			return $job->isScheduled($time);
		})));
	}

	public function getExecutable(): JobCollection
	{
		return $this
			->filterScheduled(new Time)
			->filterExpired()
			->sortByTimeStarted()
			;
	}

	public function sortByTimeStarted(): JobCollection
	{
		$array = $this->getArrayCopy();
		usort($array, function (Job $a, Job $b) {
			return $a->getTimeStarted() < $b->getTimeStarted() ? -1 : 1;
		});

		return new static($array);
	}

	public function getReversed(): JobCollection
	{
		return new static(array_reverse($this->getArrayCopy()));
	}

	public function getCallback(): callable
	{
		return function () {
			$stopwatch = new Stopwatch;
			foreach ($this->getExecutable() as $job) {
				$job->run();
				if ($stopwatch->getElapsedRatio($this->getMaxRunningSeconds()) >= 1) {
					break;
				}
			}
		};
	}

	public function getProcedure(): Procedure
	{
		return new Procedure($this->getIdentifier(), $this->getLockTimeout(), $this->getCallback());
	}

	public function run()
	{
		$this->getProcedure()->run();
	}
}
