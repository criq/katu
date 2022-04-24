<?php

namespace Katu\Tools\Calendar;

class Interval
{
	protected $start;
	protected $end;

	public function __construct(Time $start, Time $end)
	{
		$this->start = $start;
		$this->end = $end;
	}

	public function getStart(): Time
	{
		return $this->start;
	}

	public function getEnd(): Time
	{
		return $this->end;
	}

	public function getCountDays(): int
	{
		return $this->getStart()->diff($this->getEnd())->days + 1;
	}

	public function getDates(): TimeCollection
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
