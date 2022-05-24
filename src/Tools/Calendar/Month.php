<?php

namespace Katu\Tools\Calendar;

class Month
{
	public function __construct(Time $start)
	{
		$this->setStart($start);
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
}
