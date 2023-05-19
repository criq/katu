<?php

namespace Katu\Tools\Calendar;

class Year
{
	public function __toString()
	{
		return $this->getDateTime()->format("Y");
	}

	public function getStart()
	{
		return $this->getStartDay()->getStart();
	}

	public function getEndDateTime()
	{
		return clone $this->getEndDay()->getEndDateTime();
	}
}
