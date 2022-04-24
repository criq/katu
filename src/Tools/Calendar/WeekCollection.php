<?php

namespace Katu\Tools\Calendar;

class WeekCollection extends \ArrayObject
{
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
		return $this[0] ?? null;
	}

	public function getLast(): ?Week
	{
		return $this[count($this) - 1] ?? null;
	}
}
