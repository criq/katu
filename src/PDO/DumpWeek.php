<?php

namespace Katu\PDO;

class DumpWeek extends DumpDateCollection
{
	public $week;
	public $year;

	public function __construct($datetime)
	{
		$this->datetime = $datetime;
		$this->year = (int)$datetime->format("Y");
		$this->week = (int)$datetime->format("W");
	}

	public function getObsoleteDumps()
	{
		// Older than 1 month, keep the newest backup.
		if ($this->getAgeInWeeks() > 4) {
			$this->sortDumpsByTime();

			return new DumpCollection(array_slice($this->dumps, 0, -1));

		// Older than two weeks, keep newest backup from every day.
		} elseif ($this->getAgeInWeeks() > 2) {
			$dumps = new DumpCollection;
			foreach ($this->getByDay() as $day) {
				$dumps->add($day->getObsoleteDumps());
			}

			return $dumps;

		// Keep all.
		} else {
			return new DumpCollection;
		}
	}

	public function getByDay()
	{
		$days = [];

		foreach ($this as $dump) {
			if (!isset($days[$dump->datetime->format("Ymd")])) {
				$days[$dump->datetime->format("Ymd")] = new DumpDay($dump->datetime);
			}
			$days[$dump->datetime->format("Ymd")][] = $dump;
		}

		return array_values($days);
	}

	public function cleanup()
	{
		foreach ($this->getObsoleteDumps() as $dump) {
			$dump->delete();
		}

		return true;
	}
}
