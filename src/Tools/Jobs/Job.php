<?php

namespace Katu\Tools\Jobs;

use Katu\Cache\Pickle;
use Katu\Tools\Calendar\Time;
use Katu\Tools\Calendar\Timeout;
use Katu\Tools\Locks\Procedure;
use Katu\Tools\Package\Package;
use Katu\Tools\Package\PackagedInterface;
use Katu\Types\TClass;
use Katu\Types\TIdentifier;

abstract class Job implements PackagedInterface
{
	abstract public function getCallback(): callable;

	const DEFAULT_INTERVAL = "1 day";
	const DEFAULT_TIMEOUT = "1 hour";

	protected $args = [];
	protected $interval;
	protected $maxLoadAverage = 1.5;
	protected $timeout;

	public function __construct(array $args = [])
	{
		$this->setArgs($args);
	}

	public function getPackage(): Package
	{
		return new Package([
			"class" => (new TClass($this))->getPortableName(),
			"args" => $this->getArgs(),
		]);
	}

	public static function createFromPackage(Package $package): ?Job
	{
		try {
			$className = TClass::createFromPortableName($package->getPayload()["class"])->getName();

			return new $className($package->getPayload()["args"] ?? []);
		} catch (\Throwable $e) {
			return null;
		}
	}

	public function getClass(): TClass
	{
		return new TClass($this);
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
		return new Pickle(new TIdentifier(static::class, $this->getArgs(), __FUNCTION__));
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
		return new Pickle(new TIdentifier(static::class, $this->getArgs(), __FUNCTION__));
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

	public function setInterval(?Timeout $interval): Job
	{
		$this->interval = $interval;

		return $this;
	}

	public function getInterval(): Timeout
	{
		return $this->interval ?: new Timeout(static::DEFAULT_INTERVAL);
	}

	public function setTimeout(?Timeout $timeout): Job
	{
		$this->timeout = $timeout;

		return $this;
	}

	public function getTimeout(): Timeout
	{
		return $this->timeout ?: new Timeout(static::DEFAULT_TIMEOUT);
	}

	public function isExpired(): bool
	{
		if (is_null($this->getTimeStarted())) {
			return true;
		}

		if (!$this->getTimeStarted()->fitsInTimeout($this->getInterval())) {
			return true;
		}

		return false;
	}

	public function setMaxLoadAverage(?float $maxLoadAverage): Job
	{
		$this->maxLoadAverage = $maxLoadAverage;

		return $this;
	}

	public function getMaxLoadAverage(): ?float
	{
		return $this->maxLoadAverage;
	}

	public function getIdentifier(): TIdentifier
	{
		return new TIdentifier(static::class, $this->getArgs());
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
		if (\Katu\Config\Env::getPlatform() != "dev" && $this->getMaxLoadAverage() && \Katu\Tools\System\System::getLoadAveragePerCpu()[0] >= $this->getMaxLoadAverage()) {
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
