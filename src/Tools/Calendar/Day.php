<?php

namespace Katu\Tools\Calendar;

class Day extends Time
{
	public function __toString(): string
	{
		return $this->format("Y-m-d");
	}

	public function getTime(): Time
	{
		$timeClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Time::class);

		return new $timeClass($this);
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
			->modify("- 1 microsecond")
			;
	}

	public function getIndex(): int
	{
		return (int)$this->getMonth()->getStart()->format("N");
	}

	public function getWeek(): Week
	{
		$weekClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Week::class);

		return new $weekClass(clone $this);
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
		$monthClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Month::class);

		return new $monthClass(clone $this);
	}

	public function getYear(): Year
	{
		$yearClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Year::class);

		return new $yearClass(clone $this);
	}

	public function getPrevious(): Day
	{
		$dayClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Day::class);

		return new $dayClass((clone $this)->modify("- 1 day"));
	}

	public function getNext(): Day
	{
		$dayClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Day::class);

		return new $dayClass((clone $this)->modify("+ 1 day"));
	}

	public function getDaysUntil(Day $day): DayCollection
	{
		$dayClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Day::class);
		$dayCollectionClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\DayCollection::class);

		$currentDay = clone $this;
		while ($currentDay->getStart() <= $day->getStart()) {
			$days[] = clone $currentDay;
			$currentDay = new $dayClass((clone $currentDay)->modify("+ 1 day"));
		}

		return new $dayCollectionClass($days);
	}
}
