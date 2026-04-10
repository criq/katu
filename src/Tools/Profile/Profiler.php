<?php

namespace Katu\Tools\Profile;

use Katu\Tools\Calendar\Seconds;
use Katu\Tools\Profiler\Stopwatch;

/**
 * Multi-lap wall-clock profiler. Uses {@see Stopwatch} resolution and {@see Seconds} for durations.
 *
 * Use {@see ProfilerContext} so controllers, models, and Twig (via {@see ProfilerTwigExtension}) share one instance per request.
 * For output, prefer {@see ProfilerLocalLogger} (local file/stderr) instead of cloud-backed application loggers so logging does not distort timings.
 */
class Profiler
{
	protected $startedAtNano;
	protected $lastCheckpointNano;

	/** @var ProfileLapCollection */
	protected $laps;

	public function __construct()
	{
		$this->laps = new ProfileLapCollection;
		$this->restart();
	}

	public function restart(): Profiler
	{
		$now = Stopwatch::getCurrentNanoseconds();
		$this->startedAtNano = $now;
		$this->lastCheckpointNano = $now;
		$this->laps = new ProfileLapCollection;

		return $this;
	}

	/**
	 * Duration since the previous lap(), segment(), addMeasured(), or restart().
	 */
	public function lap(string $name): Seconds
	{
		$now = Stopwatch::getCurrentNanoseconds();
		$seconds = $this->nanoDeltaToSeconds($now - $this->lastCheckpointNano);
		$this->laps->addLap(new ProfileLap($name, $seconds));
		$this->lastCheckpointNano = $now;

		return $seconds;
	}

	/**
	 * Records a lap with a known duration (e.g. Twig {% profile %} blocks).
	 */
	public function addMeasured(string $name, Seconds $duration): Profiler
	{
		$this->laps->addLap(new ProfileLap($name, $duration));
		$this->lastCheckpointNano = Stopwatch::getCurrentNanoseconds();

		return $this;
	}

	/**
	 * Runs $callback and records one lap covering only the callable (exceptions still record time in finally).
	 *
	 * @template T
	 * @param callable(): T $callback
	 * @return T
	 */
	public function segment(string $name, callable $callback)
	{
		$start = Stopwatch::getCurrentNanoseconds();
		try {
			return $callback();
		} finally {
			$seconds = $this->nanoDeltaToSeconds(Stopwatch::getCurrentNanoseconds() - $start);
			$this->laps->addLap(new ProfileLap($name, $seconds));
			$this->lastCheckpointNano = Stopwatch::getCurrentNanoseconds();
		}
	}

	public function getStartedAtNano(): float
	{
		return $this->startedAtNano;
	}

	/**
	 * Wall-clock anchor in seconds since Unix epoch (same scale as microtime(true)).
	 */
	public function getStartedAt(): float
	{
		return $this->startedAtNano * (Stopwatch::FACTOR_BASE / Stopwatch::FACTOR_NANO);
	}

	public function getElapsed(): Seconds
	{
		return $this->nanoDeltaToSeconds(Stopwatch::getCurrentNanoseconds() - $this->startedAtNano);
	}

	public function getElapsedSeconds(): float
	{
		return $this->getElapsed()->getValue();
	}

	public function getLaps(): ProfileLapCollection
	{
		return $this->laps;
	}

	/**
	 * @return array<string, Seconds>
	 */
	public function getTotalsByName(): array
	{
		return $this->laps->getTotalsByName();
	}

	public function format(string $title = "profile"): string
	{
		$parts = [];
		foreach ($this->laps as $lap) {
			if (!$lap instanceof ProfileLap) {
				continue;
			}
			$parts[] = $lap->getName() . "=" . round($lap->getDuration()->getValue(), 4) . "s";
		}
		$parts[] = "total=" . round($this->getElapsed()->getValue(), 4) . "s";

		return "[" . $title . "] " . implode(" ", $parts);
	}

	protected function nanoDeltaToSeconds(float $nanoDelta): Seconds
	{
		return new Seconds($nanoDelta * (Stopwatch::FACTOR_BASE / Stopwatch::FACTOR_NANO));
	}
}
