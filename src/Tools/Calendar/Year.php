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
		return new Time($this);
	}

	public function getStartDay(): Day
	{
		return new Day((clone $this->getTime())->setMonth(1)->setDay(1));
	}

	public function getStart(): Time
	{
		return $this->getStartDay()->getStart();
	}

	public function getEndDay(): Day
	{
		return new Day(($this->getStartDay()->getStart())->modify("+ 1 year")->modify("- 1 day"));
	}

	public function getEnd(): Time
	{
		return $this->getEndDay()->getEnd();
	}
}
