<?php

namespace Katu\Tools\Calendar;

class Interval
{
	protected $start;
	protected $end;

	public function __construct(Time $start, Time $end)
	{
		if ($end < $start) {
			throw new \Katu\Exceptions\InputErrorException("End of interval is before its start.");
		}

		$this->setStart($start);
		$this->setEnd($end);
	}

	public function setStart(Time $value): Interval
	{
		$this->start = $value;

		return $this;
	}

	public function getStart(): Time
	{
		return $this->start;
	}

	public function setEnd(Time $value): Interval
	{
		$this->end = $value;

		return $this;
	}

	public function getEnd(): Time
	{
		return $this->end;
	}

	public function getDays(): TimeCollection
	{
		$res = new TimeCollection;

		$day = clone $this->getStart();
		while ($day <= $this->getEnd()) {
			$res[] = $day;

			$day = (clone $day)->modify("+ 1 day");
		}

		return $res;
	}

	public function getIntersection(Interval $interval): ?Interval
	{
		$start = max($this->getStart(), $interval->getStart());
		$end = min($this->getEnd(), $interval->getEnd());

		try {
			return new static($start, $end);
		} catch (\Throwable $e) {
			return null;
		}
	}

	public function getSeconds(): Seconds
	{
		return new Seconds($this->getEnd()->getTimestamp() - $this->getStart()->getTimestamp());
	}

	public static function validate(\Katu\Tools\Validation\Param $startParam, \Katu\Tools\Validation\Param $endParam)
	{
		$result = new \Katu\Tools\Validation\Result;

		return $result;
	}
}
