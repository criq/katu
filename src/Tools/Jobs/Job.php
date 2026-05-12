<?php

namespace Katu\Tools\Jobs;

use App\Config\AppConfig;
use Katu\Cache\Pickle;
use Katu\Tools\Calendar\Time;
use Katu\Tools\Calendar\Timeout;
use Katu\Tools\Locks\Procedure;
use Katu\Tools\Package\Package;
use Katu\Tools\Package\PackagedInterface;
use Katu\Types\TClass;
use Katu\Types\TIdentifier;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Job implements PackagedInterface
{
	abstract public function getCallback(): callable;

	const DEFAULT_INTERVAL = "1 day";
	const DEFAULT_TIMEOUT = "1 hour";

	protected $args = [];
	protected $consoleInput;
	protected $consoleOutput;
	protected $id;
	protected $interval;
	protected $isLockChecked = true;
	protected $limit;
	protected $maxLoadAverage = 1.5;
	protected $processed = 0;
	protected $schedules;
	protected $timeout;
	protected $total;

	/**
	 * Process-wide bypass for the load-average gate in {@see self::run()}.
	 *
	 * Set to true by interactive runners (e.g. the `jobs:run` CLI command in v2's
	 * `RunFromCLI`) so manual runs don't get rejected by the per-job
	 * {@see self::getMaxLoadAverage()} cap, which subclasses sometimes override
	 * with a hard-coded constant that masks {@see self::setMaxLoadAverage()}.
	 * Cron / scheduled runs leave this flag at false so the cap still applies.
	 */
	protected static $ignoreMaxLoadAverage = false;

	public static function setIgnoreMaxLoadAverage(bool $ignore): void
	{
		self::$ignoreMaxLoadAverage = $ignore;
	}

	public static function getIgnoreMaxLoadAverage(): bool
	{
		return self::$ignoreMaxLoadAverage;
	}

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

	public function getTitle(): string
	{
		return $this->getClass()->getPortableName();
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

	public function setIsLockChecked(bool $isLockChecked): Job
	{
		$this->isLockChecked = $isLockChecked;

		return $this;
	}

	public function getIsLockChecked(): bool
	{
		return $this->isLockChecked;
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
		return (new Procedure($this->getIdentifier(), $this->getTimeout(), $this->getCallback()))
			->setIsLockChecked($this->getIsLockChecked())
			;
	}

	public function setSchedules(ScheduleCollection $schedules)
	{
		$this->schedules = $schedules;

		return $this;
	}

	public function getSchedules(): ScheduleCollection
	{
		return $this->schedules ?: $this->getDefaultSchedules() ?: new ScheduleCollection([
			new Schedule,
		]);
	}

	public function getDefaultSchedules(): ?ScheduleCollection
	{
		return null;
	}

	public function isScheduled(Time $time): bool
	{
		foreach ($this->getSchedules() as $schedule) {
			if (preg_match("/^{$schedule->getRegexp()}$/", $time->format("Y m d H i"))) {
				return true;
			}
		}

		return false;
	}

	public function isRunning(): bool
	{
		return $this->getTimeStarted() && (!$this->getTimeFinished() || $this->getTimeFinished() < $this->getTimeStarted());
	}

	public function getId(): string
	{
		if (is_null($this->id)) {
			$this->id = uniqid();
		}

		return $this->id;
	}

	public function run(): bool
	{
		$logger = \App\App::getLogger(new TIdentifier(__CLASS__));
		$loggerContext = [
			"job" => $this->getTitle(),
			"id" => $this->getId(),
			"args" => serialize($this->getArgs()),
		];

		try {
			// Check lock.
			if (!$this->getProcedure()->getIsExecutable()) {
				$message = "Job {$this->getTitle()} locked.";
				$logger->notice($message, array_merge($loggerContext));
				$this->outputLine($message);

				return false;
			}

			// Check max load average. Bypass when running in DEVELOPMENT or when an
			// interactive runner has set the process-wide ignore flag (e.g. `jobs:run`).
			$maxLoadAverage = $this->getMaxLoadAverage();
			$loadAverage = \Katu\Tools\System\System::getLoadAveragePerCpu()[0];
			if (!self::$ignoreMaxLoadAverage && !(new AppConfig)->getIsEnvironment("DEVELOPMENT") && $maxLoadAverage && $loadAverage >= $maxLoadAverage) {
				$message = "Load average {$loadAverage} above {$maxLoadAverage}.";
				$logger->notice($message);
				$this->outputLine($message);

				return false;
			}

			$this->setTimeStarted(new Time);
			$message = "Job {$this->getTitle()} started.";
			$logger->info($message, array_merge($loggerContext, [
				"event" => "job.start",
			]));
			$this->outputLine($message);

			$this->getProcedure()->run();

			$this->setTimeFinished(new Time);
			$message = "Job {$this->getTitle()} finished.";
			$logger->info($message, array_merge($loggerContext, [
				"event" => "job.end",
			]));
			$this->outputLine($message);

			return true;
		} catch (\Throwable $e) {
			$logger->error($e);
			$this->outputLine($e->getMessage());

			return false;
		}
	}

	public function setLimit(?int $limit): Job
	{
		$this->limit = $limit;

		return $this;
	}

	public function getLimit(): ?int
	{
		return $this->limit;
	}

	public function getDefaultLimit(): ?int
	{
		return null;
	}

	public function getResolvedLimit(): ?int
	{
		return $this->getLimit() ?: $this->getDefaultLimit();
	}

	public function incrementProcessed(int $increment = 1): Job
	{
		$this->setProcessed($this->getProcessed() + $increment);

		return $this;
	}

	public function setProcessed(int $processed): Job
	{
		$this->processed = $processed;

		return $this;
	}

	public function getProcessed(): int
	{
		return $this->processed;
	}

	public function getRemaining(): ?int
	{
		return $this->getResolvedLimit() ? ($this->getResolvedLimit() - $this->getProcessed()) : null;
	}

	public function setTotal(?int $total): Job
	{
		$this->total = $total;

		return $this;
	}

	public function getTotal(): ?int
	{
		return $this->total;
	}

	public function getTotalPickle(): Pickle
	{
		return new Pickle(new TIdentifier(static::class, __FUNCTION__));
	}

	public function getPickledTotal(): ?int
	{
		return $this->getTotalPickle()->get();
	}

	public function setPickledTotal(?int $total): Job
	{
		$this->getTotalPickle()->set($total);

		return $this;
	}

	public function getResolvedTotal(): ?int
	{
		return $this->getTotal() ?: $this->getPickledTotal();
	}

	public function getProgress(): ?float
	{
		if ($this->getResolvedTotal()) {
			return $this->getProcessed() / $this->getResolvedTotal();
		}

		return null;
	}

	public function canProcess(): bool
	{
		return is_null($this->getRemaining()) || $this->getRemaining() > 0;
	}

	public function setConsoleInput(?InputInterface $input): Job
	{
		$this->consoleInput = $input;

		return $this;
	}

	public function getConsoleInput(): ?InputInterface
	{
		return $this->consoleInput;
	}

	public function setConsoleOutput(?OutputInterface $output): Job
	{
		$this->consoleOutput = $output;

		return $this;
	}

	public function getConsoleOutput(): ?OutputInterface
	{
		return $this->consoleOutput;
	}

	public function outputLine(string $string): bool
	{
		if ($this->getConsoleOutput()) {
			$this->getConsoleOutput()->writeln($string);

			return true;
		}

		return false;
	}
}
