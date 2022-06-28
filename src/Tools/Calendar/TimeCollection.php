<?php

namespace Katu\Tools\Calendar;

class TimeCollection extends \ArrayObject
{
	public function sortAscending(): TimeCollection
	{
		$array = $this->getArrayCopy();

		usort($array, function (Time $a, Time $b) {
			return $a->getTimestamp() > $b->getTimestamp() ? 1 : -1;
		});

		return new static($array);
	}

	public function getUniqueDays(): TimeCollection
	{
		return new static(array_map(function (string $date) {
			return new Time($date);
		}, array_unique(array_map(function (Time $time) {
			return $time->format("Y-m-d");
		}, $this->getArrayCopy()))));
	}
}
