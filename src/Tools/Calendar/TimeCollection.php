<?php

namespace Katu\Tools\Calendar;

class TimeCollection extends \ArrayObject
{
	public function sortAscending(): TimeCollection
	{
		$timeCollectionClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\TimeCollection::class);

		$array = $this->getArrayCopy();

		usort($array, function (Time $a, Time $b) {
			return $a->getTimestamp() > $b->getTimestamp() ? 1 : -1;
		});

		return new $timeCollectionClass($array);
	}

	public function getUniqueDays(): TimeCollection
	{
		$timeClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Time::class);
		$timeCollectionClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\TimeCollection::class);

		return new $timeCollectionClass(array_map(function (string $date) use ($timeClass) {
			return new $timeClass($date);
		}, array_unique(array_map(function (Time $time) {
			return $time->format("Y-m-d");
		}, $this->getArrayCopy()))));
	}

	public function getDates(): array
	{
		return array_map(function(Time $time) {
			return $time->format("Y-m-d");
		}, $this->getArrayCopy());
	}
}
