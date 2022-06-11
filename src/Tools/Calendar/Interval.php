<?php

namespace Katu\Tools\Calendar;

use Katu\Types\TClass;

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

	public static function validate(\Katu\Tools\Validation\Param $startParam, \Katu\Tools\Validation\Param $endParam)
	{
		$result = new \Katu\Tools\Validation\Validation;

		if (!trim($startParam)) {
			$result->addError(
				(new \Katu\Errors\Error("Chybějící začátek intervalu."))
					->addParam($startParam)
			);
		} else {
			$start = static::getTimeClass()->getName()::createFromString($startParam);
			if (!$start) {
				$result->addError(
					(new \Katu\Errors\Error("Neplatný začátek intervalu."))
						->addParam($startParam)
				);
			}
		}

		if (!trim($endParam)) {
			$result->addError(
				(new \Katu\Errors\Error("Chybějící konec intervalu."))
					->addParam($endParam)
			);
		} else {
			$end = static::getTimeClass()->getName()::createFromString($endParam);
			if (!$end) {
				$result->addError(
					(new \Katu\Errors\Error("Neplatný konec intervalu."))
						->addParam($endParam)
				);
			}
		}

		if ($start && $end && $start > $end) {
			$result->addError(
				(new \Katu\Errors\Error("Začátek intervalu je později než jeho konec."))
					->addParam($startParam)
					->addParam($endParam)
			);
		} elseif ($start && $end) {
			$result->setResponse(new static($start, $end));
		}

		return $result;
	}

	public static function getTimeClass(): TClass
	{
		return new TClass("Katu\Tools\Calendar\Time");
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

	public function getMonths(): MonthCollection
	{
		$res = new MonthCollection;

		$time = (clone $this->getStart())->setDay(1);
		while ($time <= $this->getEnd()) {
			$res[] = new Month($time);
			$time = $time->modify("+ 1 month");
		}

		return $res;
	}
}
