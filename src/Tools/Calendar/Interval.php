<?php

namespace Katu\Tools\Calendar;

use Katu\Tools\Validation\Param;
use Katu\Tools\Validation\Validation;

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

	public static function validate(Param $startParam, Param $endParam): Validation
	{
		$timeClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Time::class);
		$intervalClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Interval::class);

		$result = new \Katu\Tools\Validation\Validation;
		$start = null;
		$end = null;

		if (!trim($startParam)) {
			$result->addError(
				(new \Katu\Errors\Error("Chybějící začátek intervalu."))
					->addParam($startParam)
			);
		} else {
			$start = $timeClass::createFromString($startParam);
			if (!$start) {
				$result->addError(
					(new \Katu\Errors\Error("Neplatný začátek intervalu."))
						->addParam($startParam)
				);
			}
		}

		if (trim($endParam)) {
			$end = $timeClass::createFromString($endParam);
			if (!$end) {
				$result->addError(
					(new \Katu\Errors\Error("Neplatný konec intervalu."))
						->addParam($endParam)
				);
			}
		}

		if ($start && !$end) {
			$end = clone $start;
		}

		if ($start && $end && $start > $end) {
			$result->addError(
				(new \Katu\Errors\Error("Začátek intervalu je později než jeho konec."))
					->addParam($startParam)
					->addParam($endParam)
			);
		} elseif ($start && $end) {
			$result->setResponse(new $intervalClass($start, $end));
		}

		return $result;
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

	public function getMonths(): MonthCollection
	{
		$monthCollectionClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\MonthCollection::class);
		$monthClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Month::class);

		$res = new $monthCollectionClass;

		$time = (clone $this->getStart())->setDay(1);
		while ($time <= $this->getEnd()) {
			$res[] = new $monthClass($time);
			$time = $time->modify("+ 1 month");
		}

		return $res;
	}

	public function getDays(): TimeCollection
	{
		$timeCollectionClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\TimeCollection::class);

		$res = new $timeCollectionClass;

		$day = clone $this->getStart();
		while ($day <= $this->getEnd()) {
			$res[] = $day;

			$day = (clone $day)->modify("+ 1 day");
		}

		return $res;
	}

	public function getSeconds(): Seconds
	{
		$secondsClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Seconds::class);

		return new $secondsClass($this->getEnd()->getTimestamp() - $this->getStart()->getTimestamp());
	}

	public function getIntersection(Interval $interval): ?Interval
	{
		$intervalClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Interval::class);

		$start = max($this->getStart(), $interval->getStart());
		$end = min($this->getEnd(), $interval->getEnd());

		try {
			return new $intervalClass($start, $end);
		} catch (\Throwable $e) {
			return null;
		}
	}

	public function subtract(Interval $subtract): IntervalCollection
	{
		$intervalCollectionClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\IntervalCollection::class);

		var_dump($this);
		var_dump($subtract);
		die;
	}

	public function fitsTime(Time $time): bool
	{
		return $this->getStart() <= $time && $this->getEnd() >= $time;
	}
}
