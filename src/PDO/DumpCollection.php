<?php

namespace Katu\PDO;

class DumpCollection implements \Iterator, \ArrayAccess
{
	public $dumps = [];
	protected $iteratorPosition = 0;

	public function __construct($dumps = [])
	{
		$this->dumps = $dumps;
	}

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
			if (!isset($dumps[$dump->database])) {
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
			if (!isset($weeks[$dump->datetime->format('YW')])) {
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
		// var_dump(\Katu\Utils\FileSystem::getSpaceAboveFreeTreshold(.9)->inGB());

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

	/****************************************************************************
	 * ArrayAccess.
	 */
	public function offsetSet($offset, $value)
	{
		if (is_null($offset)) {
			$this->dumps[] = $value;
		} else {
			$this->dumps[$offset] = $value;
		}
	}

	public function offsetExists($offset)
	{
		return isset($this->dumps[$offset]);
	}

	public function offsetUnset($offset)
	{
		unset($this->dumps[$offset]);
	}

	public function offsetGet($offset)
	{
		return isset($this->dumps[$offset]) ? $this->dumps[$offset] : null;
	}
}
