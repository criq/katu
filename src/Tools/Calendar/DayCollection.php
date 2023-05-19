<?php

namespace Katu\Tools\Calendar;

class DayCollection extends \ArrayObject
{
	public function createDay(Day $day): DayCollection
	{
		if (array_search((string)$day, $this->getArrayCopy()) === false) {
			$this[] = $day;
		}

		return $this;
	}

	public function getDay(Day $day): ?Day
	{
		$key = array_search((string)$day, $this->getArrayCopy());
		if ($key !== false) {
			return $this[$key];
		}

		return null;
	}

	public function getOrCreateDay(Day $day): Day
	{
		if (!$this->getDay($day)) {
			$this->createDay($day);
		}

		return $this->getDay($day);
	}

	public function getMonths(): MonthCollection
	{
		$months = new MonthCollection;
		foreach ($this as $day) {
			$months->getOrCreateMonth($day->getMonth());
		}

		return $months;
	}

	public function getWeeks(): WeekCollection
	{
		$weeks = new WeekCollection;
		foreach ($this as $day) {
			$weeks->getOrCreateWeek($day->getWeek());
		}

		return $weeks;
	}
}
