<?php

namespace Katu\Tools\Calendar;

class Month
{
	protected $start;

	public function __construct(Time $start)
	{
		$this->setStart($start);
	}

	public function __toString()
	{
		return $this->getStart()->format("Y-m");
	}

	public function setStart(Time $start): Month
	{
		$this->start = (clone $start)->setDay(1)->setTime(0, 0, 0);

		return $this;
	}

	public function getStart(): Time
	{
		return $this->start;
	}

	public function getStartDay(): Day
	{
		return new Day($this->getStart());
	}

	public function getEnd(): Time
	{
	}

	public function getEndDay(): Day
	{
		return (clone $this->getStart())->modify("+ 1 month")
	}

	public function getWeeks(): WeekCollection
	{
		$weeks = new WeekCollection;

		$startDay = $this->getStart()->getWeek()->getStartDay();
		$endDay = $this->getEnd()->getWeek()->getEndDay();
		$currentDay = clone $startDay;

		while ($currentDay->getDateTime() <= $endDay->getDateTime()) {
			if ((int)$currentDay->getDateTime()->format("N") == 1) {
				$weeks[] = new Week($currentDay->getDateTime());
			}
			$currentDay = new Day((clone $currentDay->getDateTime())->modify("+ 1 day"));
		}

		return $weeks;
	}

	public function getDays(): Days
	{
		$days = new Days;

		$startDay = $this->getStartDay();
		$endDay = $this->getEndDay();
		$currentDay = clone $startDay;

		while ($currentDay->getDateTime() <= $endDay->getDateTime()) {
			$days[] = new Day($currentDay->getDateTime());
			$currentDay = new Day((clone $currentDay->getDateTime())->modify("+ 1 day"));
		}

		return $days;
	}

	public function getPrevious()
	{
		return new static((clone $this->getDateTime())->modify("-1 month"));
	}

	public function getNext()
	{
		return new static((clone $this->getDateTime())->modify("+1 month"));
	}
}
