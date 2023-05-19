<?php

namespace Katu\Tools\Calendar;

class Day extends Time
{
	protected $events;

	public function __toString()
	{
		return $this->format("Y-m-d");
	}

	public function getStart(): Time
	{
		return (clone $this)
			->setTime(0, 0, 0)
			;
	}

	public function getEnd(): Time
	{
		return (clone $this)
			->modify("+ 1 day")
			->setTime(0, 0, 0)
			->modify("- 1 second")
			;
	}

	public function getIndex(): int
	{
		return (int)$this->getMonth()->getStart()->format("N");
	}

	public function getWeek(): Week
	{
		return new Week(clone $this);
	}

	public function getWeekStartOffset(): int
	{
		return ((int)$this->format("N") - 1) * -1;
	}

	public function getWeekEndOffset(): int
	{
		return 7 - (int)$this->format("N");
	}

	public function getMonth(): Month
	{
		return new Month(clone $this);
	}

	public function getYear(): Year
	{
		return new Year(clone $this);
	}

	public function getPrevious(): Day
	{
		return new static((clone $this)->modify("- 1 day"));
	}

	public function getNext(): Day
	{
		return new static((clone $this)->modify("+ 1 day"));
	}

	public function getDaysUntil(Day $day): DayCollection
	{
		$currentDay = clone $this;
		while ($currentDay->getStart() <= $day->getStart()) {
			$days[] = clone $currentDay;
			$currentDay = new static((clone $currentDay)->modify("+ 1 day"));
		}

		return new DayCollection($days);
	}
}
