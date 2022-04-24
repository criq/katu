<?php

namespace Katu\PDO;

class DumpCollection extends \ArrayObject
{
	public static function getAll()
	{
		$dumps = new static;
		$dumps->dumps = Dump::getAll();

		return $dumps;
	}

	public function add(DumpCollection $dumps)
	{
		$this->dumps = array_merge($this->dumps, $dumps->dumps);

		return true;
	}

	public static function getByDatabase()
	{
		$dumps = [];

		foreach (static::getAll() as $dump) {
			if (!($dumps[$dump->database] ?? null)) {
				$dumps[$dump->database] = new static;
			}
			$dumps[$dump->database][] = $dump;
		}

		return $dumps;
	}

	public function getByWeek()
	{
		$weeks = [];

		foreach ($this as $dump) {
			if (!($weeks[$dump->datetime->format('YW')] ?? null)) {
				$weeks[$dump->datetime->format('YW')] = new DumpWeek($dump->datetime);
			}
			$weeks[$dump->datetime->format('YW')][] = $dump;
		}

		return array_values($weeks);
	}

	public function sortDumpsByTime()
	{
		array_multisort($this->dumps, array_map(function ($i) {
			return $i->datetime->getTimestamp();
		}, $this->dumps), $this->dumps, \SORT_NUMERIC);
	}

	public function cleanup()
	{
		// If disk usage more than 90 %, remove some backups.
		$weeks = $this->getByWeek();
		foreach ($weeks as $week) {
			$week->cleanup();
		}

		return true;
	}

	/****************************************************************************
	 * Iterator.
	 */
	public function rewind()
	{
		$this->iteratorPosition = 0;
	}

	public function current()
	{
		return $this->dumps[$this->iteratorPosition];
	}

	public function key()
	{
		return $this->iteratorPosition;
	}

	public function next()
	{
		++$this->iteratorPosition;
	}

	public function valid()
	{
		return isset($this->dumps[$this->iteratorPosition]);
	}
}
