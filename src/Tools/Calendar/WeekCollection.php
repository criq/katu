<?php

namespace Katu\Tools\Calendar;

class WeekCollection extends \ArrayObject
{
	public function createWeek(Week $week): Weeks
	{
		if (array_search((string)$week, $this->getArrayCopy()) === false) {
			$this[] = $week;
		}

		return $this;
	}

	public function getWeek(Week $week): ?Week
	{
		$key = array_search((string)$week, $this->getArrayCopy());
		if ($key !== false) {
			return $this[$key];
		}

		return null;
	}

	public function getOrCreateWeek(Week $week)
	{
		if (!$this->getWeek($week)) {
			$this->createWeek($week);
		}

		return $this->getWeek($week);
	}
	
	public function sortAscending(): WeekCollection
	{
		$array = $this->getArrayCopy();

		usort($array, function (Week $a, Week $b) {
			return $a->getStart() > $b->getStart() ? 1 : -1;
		});

		return new static($array);
	}

	public function getFirst(): ?Week
	{
		return array_values($this->getArrayCopy())[0] ?? null;
	}

	public function getLast(): ?Week
	{
		return $this[count($this) - 1] ?? null;
	}
}
