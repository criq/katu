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
		$timeClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Time::class);

		return new $timeClass($this);
	}

	public function getStartDay(): Day
	{
		$dayClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Day::class);

		return new $dayClass($this->getTime()->setDay(1));
	}

	public function getStart(): Time
	{
		return $this->getStartDay()->getStart();
	}

	public function getEndDay(): Day
	{
		$dayClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Day::class);

		return new $dayClass((clone $this->getStart())->modify("+ 1 month")->modify("- 1 day"));
	}

	public function getEnd(): Time
	{
		return $this->getEndDay()->getEnd();
	}

	public function getWeeks(): WeekCollection
	{
		$weekCollectionClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\WeekCollection::class);
		$weekClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Week::class);
		$dayClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Day::class);

		$weeks = new $weekCollectionClass;

		$startDay = $this->getStartDay()->getWeek()->getStartDay();
		$endDay = $this->getEndDay()->getWeek()->getEndDay();
		$currentDay = clone $startDay;

		while ($currentDay <= $endDay) {
			if ((int)$currentDay->format("N") == 1) {
				$weeks[] = new $weekClass($currentDay);
			}
			$currentDay = new $dayClass((clone $currentDay)->modify("+ 1 day"));
		}

		return $weeks;
	}

	public function getDays(): DayCollection
	{
		$dayCollectionClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\DayCollection::class);
		$dayClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Day::class);

		$days = new $dayCollectionClass;

		$startDay = $this->getStartDay();
		$endDay = $this->getEndDay();
		$currentDay = clone $startDay;

		while ($currentDay->getStart() <= $endDay->getStart()) {
			$days[] = (clone $currentDay);
			$currentDay = new $dayClass((clone $currentDay->getStart())->modify("+ 1 day"));
		}

		return $days;
	}

	public function getPrevious(): Month
	{
		$monthClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Month::class);

		return new $monthClass((clone $this->getTime())->modify("-1 month"));
	}

	public function getNext(): Month
	{
		$monthClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Month::class);

		return new $monthClass((clone $this->getTime())->modify("+1 month"));
	}
}
