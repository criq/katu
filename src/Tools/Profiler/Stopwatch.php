<?php

namespace Katu\Tools\Profiler;

class Stopwatch {

	const FACTOR_NANO  = 1000000000;
	const FACTOR_MICRO = 1000000;
	const FACTOR_MILLI = 1000;
	const FACTOR_BASE  = 1;

	public $start;

	public function __construct() {
		$this->start = (float) (DateTime::getMicrotime() * static::FACTOR_NANO);
	}

	public function getNanoValues() {
		return [$this->start, (float) (DateTime::getMicrotime() * static::FACTOR_NANO)];
	}

	public function getNanoDuration() {
		$values = $this->getNanoValues();

		return (float) ((max($values) - min($values)));
	}

	public function getMicroDuration() {
		return (float) ($this->getNanoDuration() * (static::FACTOR_MICRO / static::FACTOR_NANO));
	}

	public function getMilliDuration() {
		return (float) ($this->getNanoDuration() * (static::FACTOR_MILLI / static::FACTOR_NANO));
	}

	public function getDuration() {
		return (float) ($this->getNanoDuration() * (static::FACTOR_BASE / static::FACTOR_NANO));
	}

}
