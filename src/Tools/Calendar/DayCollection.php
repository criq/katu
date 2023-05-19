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

	public function getUnique(): DayCollection
	{
		return new static(array_values(array_unique($this->getArrayCopy())));
	}

	public function sortAscending(): DayCollection
	{
		$array = $this->getArrayCopy();
		usort($array, function (Day $a, Day $b) {
			return $a > $b ? 1 : -1;
		});

		return new static($array);
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
