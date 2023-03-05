<?php

namespace Katu\Tools\Jobs;

class Schedule
{
	protected $minutes;
	protected $hours;
	protected $days;
	protected $months;

	public function __construct(?array $minutes = null, ?array $hours = null, ?array $days = null, ?array $months = null)
	{
		$this->minutes = $minutes;
		$this->hours = $hours;
		$this->days = $days;
		$this->months = $months;
	}

	public function getMinutes(): array
	{
		return $this->minutes ?: range(0, 59);
	}

	public function getHours(): array
	{
		return $this->hours ?: range(0, 23);
	}

	public function getDays(): array
	{
		return $this->days ?: range(0, 31);
	}

	public function getMonths(): array
	{
		return $this->months ?: range(0, 12);
	}

	public static function getRangeRegexp(array $range): string
	{
		return "(" . implode("|", array_map(function (string $item) {
			return sprintf("%02d", $item);
		}, $range)) . ")";
	}

	public function getRegexp(): string
	{
		return implode("\s*", [
			static::getRangeRegexp($this->getMonths()),
			static::getRangeRegexp($this->getDays()),
			static::getRangeRegexp($this->getHours()),
			static::getRangeRegexp($this->getMinutes()),
		]);
	}
}
