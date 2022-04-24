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
}
