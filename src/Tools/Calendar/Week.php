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
		$timeClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Time::class);

		return new $timeClass($this);
	}

	public function getStartDay(): Day
	{
		$dayClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Day::class);

		return new $dayClass((clone $this->getTime())->getThisWeekday("Monday"));
	}

	public function getStart(): Time
	{
		return $this->getStartDay()->getStart();
	}

	public function getEndDay(): Day
	{
		$dayClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Day::class);

		return new $dayClass((clone $this->getTime())->getThisWeekday("Sunday"));
	}

	public function getEnd(): Time
	{
		return $this->getEndDay()->getEnd();
	}

	public function getInterval(): Interval
	{
		$intervalClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Interval::class);

		return new $intervalClass($this->getStart(), $this->getEnd());
	}

	public function getDays(): DayCollection
	{
		$dayClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Day::class);
		$dayCollectionClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\DayCollection::class);

		$res = new $dayCollectionClass;

		$date = clone $this->getStart();
		while ($date <= $this->getEnd()) {
			$res[] = new $dayClass($date);
			$date = (clone $date)->modify("+ 1 day");
		}

		return $res;
	}
}
