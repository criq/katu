<?php

namespace Katu\Tools\Calendar;

class Week
{
	protected $start;

	public function __construct(Time $start)
	{
		$this->setStart($start);
	}

	public function __toString(): string
	{
		return $this->getStart()->format("oW");
	}

	public function setStart(Time $value): Week
	{
		$this->start = (clone $value)->getThisWeekday("Monday")->setTime(0, 0, 0);

		return $this;
	}

	public function getStart(): Time
	{
		return $this->start;
	}

	public function getEnd(): Time
	{
		return (clone $this->getStart())->getThisWeekday("Sunday");
	}

	public function getDays(): TimeCollection
	{
		$res = new TimeCollection;

		$date = clone $this->getStart();
		while ($date <= $this->getEnd()) {
			$res[] = $date;
			$date = (clone $date)->modify("+ 1 day");
		}

		return $res;
	}
}
