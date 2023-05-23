<?php

namespace Katu\Tools\Calendar;

class Year extends Time
{
	protected $time;

	public function __toString(): string
	{
		return $this->getTime()->format("Y");
	}

	public function getTime(): Time
	{
		$timeClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Time::class);

		return new $timeClass($this);
	}

	public function getStartDay(): Day
	{
		$dayClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Day::class);

		return new $dayClass((clone $this->getTime())->setMonth(1)->setDay(1));
	}

	public function getStart(): Time
	{
		return $this->getStartDay()->getStart();
	}

	public function getEndDay(): Day
	{
		$dayClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Day::class);

		return new $dayClass(($this->getStartDay()->getStart())->modify("+ 1 year")->modify("- 1 day"));
	}

	public function getEnd(): Time
	{
		return $this->getEndDay()->getEnd();
	}
}
