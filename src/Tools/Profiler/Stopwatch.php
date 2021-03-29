<?php

namespace Katu\Tools\Profiler;

class Stopwatch
{
	const FACTOR_NANO  = 1000000000;
	const FACTOR_MICRO = 1000000;
	const FACTOR_MILLI = 1000;
	const FACTOR_BASE  = 1;

	protected $start;
	protected $finish;

	public function __construct()
	{
		$this->start();
	}

	public function __toString()
	{
		return (string)$this->getDuration();
	}

	public static function getCurrentMictorime() : float
	{
		return (float)(\Katu\Tools\DateTime\DateTime::getMicrotime() * static::FACTOR_NANO);
	}

	public function start() : Stopwatch
	{
		$this->start = static::getCurrentMictorime();

		return $this;
	}

	public function finish() : Stopwatch
	{
		$this->finish = static::getCurrentMictorime();
		$this->duration = $this->getDuration();

		return $this;
	}

	public function getStart() : ?float
	{
		return $this->start;
	}

	public function getFinish() : ?float
	{
		return $this->finish;
	}

	public function getNanoValues() : array
	{
		return [$this->getStart(), $this->getFinish() ?: static::getCurrentMictorime()];
	}

	public function getDuration() : float
	{
		return (float)($this->getNanoDuration() * (static::FACTOR_BASE / static::FACTOR_NANO));
	}

	public function getNanoDuration() : float
	{
		$values = $this->getNanoValues();

		return (float)((max($values) - min($values)));
	}

	public function getMicroDuration() : float
	{
		return (float)($this->getNanoDuration() * (static::FACTOR_MICRO / static::FACTOR_NANO));
	}

	public function getMilliDuration() : float
	{
		return (float)($this->getNanoDuration() * (static::FACTOR_MILLI / static::FACTOR_NANO));
	}
}
