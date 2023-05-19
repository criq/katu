<?php

namespace Katu\Tools\Calendar;

class Week extends Time
{
	protected $time;

	public function __toString(): string
	{
		return $this->getTime()->format("oW");
	}

	public function getTime(): Time
	{
		return new Time($this);
	}

	public function getStartDay(): Day
	{
		return new Day((clone $this->getTime())->getThisWeekday("Monday"));
	}

	public function getStart(): Time
	{
		return $this->getStartDay()->getStart();
	}

	public function getEndDay(): Day
	{
		return new Day((clone $this->getTime())->getThisWeekday("Sunday"));
	}

	public function getEnd(): Time
	{
		return $this->getEndDay()->getEnd();
	}

	public function getDays(): DayCollection
	{
		$res = new DayCollection;

		$date = clone $this->getStart();
		while ($date <= $this->getEnd()) {
			$res[] = new Day($date);
			$date = (clone $date)->modify("+ 1 day");
		}

		return $res;
	}
}
