<?php

namespace Katu\Tools\Calendar;

class MonthCollection extends \ArrayObject
{
	public function createMonth(Month $month): MonthCollection
	{
		if (array_search((string)$month, $this->getArrayCopy()) === false) {
			$this[] = $month;
		}

		return $this;
	}

	public function getMonth(Month $month): ?Month
	{
		$key = array_search((string)$month, $this->getArrayCopy());
		if ($key !== false) {
			return $this[$key];
		}

		return null;
	}

	public function sort(): MonthCollection
	{
		$array = $this->getArrayCopy();
		usort($array, function (Month $a, Month $b) {
			return $a->getTime() < $b->getTime() ? -1 : 1;
		});

		return new static($array);
	}

	public function getReversed(): MonthCollection
	{
		return new static(array_reverse($this->getArrayCopy()));
	}

	public function getOrCreateMonth(Month $month): ?Month
	{
		if (!$this->getMonth($month)) {
			$this->createMonth($month);
		}

		return $this->getMonth($month);
	}
}
