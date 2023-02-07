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
}
