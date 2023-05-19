<?php

namespace Katu\Tools\Calendar;

class Month extends Time
{
	protected $time;

	public function __toString()
	{
		return $this->getTime()->format("Y-m");
	}

	public function getTime(): Time
	{
		return new Time($this);
	}

	public function getStartDay(): Day
	{
		return new Day($this->getTime()->setDay(1));
	}

	public function getStart(): Time
	{
		return $this->getStartDay()->getStart();
	}

	public function getEndDay(): Day
	{
		return new Day((clone $this->getStart())->modify("+ 1 month")->modify("- 1 day"));
	}

	public function getEnd(): Time
	{
		return $this->getEndDay()->getEnd();
	}

	public function getWeeks(): WeekCollection
	{
		$weeks = new WeekCollection;

		$startDay = $this->getStartDay()->getWeek()->getStartDay();
		$endDay = $this->getEndDay()->getWeek()->getEndDay();
		$currentDay = clone $startDay;

		while ($currentDay <= $endDay) {
			if ((int)$currentDay->format("N") == 1) {
				$weeks[] = new Week($currentDay);
			}
			$currentDay = new Day((clone $currentDay)->modify("+ 1 day"));
		}

		return $weeks;
	}

	public function getDays(): DayCollection
	{
		$days = new DayCollection;

		$startDay = $this->getStartDay();
		$endDay = $this->getEndDay();
		$currentDay = clone $startDay;

		while ($currentDay->getStart() <= $endDay->getStart()) {
			$days[] = (clone $currentDay);
			$currentDay = new Day((clone $currentDay->getStart())->modify("+ 1 day"));
		}

		return $days;
	}

	public function getPrevious(): Month
	{
		return new static((clone $this->getTime())->modify("-1 month"));
	}

	public function getNext(): Month
	{
		return new static((clone $this->getTime())->modify("+1 month"));
	}
}
