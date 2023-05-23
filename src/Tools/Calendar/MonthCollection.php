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

	public function getOrCreateMonth(Month $month): ?Month
	{
		if (!$this->getMonth($month)) {
			$this->createMonth($month);
		}

		return $this->getMonth($month);
	}
}
