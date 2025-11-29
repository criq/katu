<?php

namespace Katu\Tools\Calendar;

use Katu\Errors\ErrorVersionCollection;
use Katu\Tools\Options\OptionCollection;
use Katu\Tools\Rest\RestResponse;
use Katu\Tools\Rest\RestResponseInterface;
use Katu\Tools\Validation\Param;
use Katu\Tools\Validation\Validation;
use Psr\Http\Message\ServerRequestInterface;

class Interval implements RestResponseInterface
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

	public function __toString(): string
	{
		return $this->getSeconds()->getValue();
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
				(new \Katu\Errors\Error("Chybějící začátek intervalu.", "MISSING_INTERVAL_START", ErrorVersionCollection::createFromArray([
					"cs" => "Chybějící začátek intervalu.",
					"sk" => "Chýbajúci začiatok intervalu.",
					"en" => "Missing interval start.",
				])))
					->addParam($startParam)
			);
		} else {
			$start = $timeClass::createFromString($startParam);
			if (!$start) {
				$result->addError(
					(new \Katu\Errors\Error("Neplatný začátek intervalu.", "INVALID_INTERVAL_START", ErrorVersionCollection::createFromArray([
						"cs" => "Neplatný začátek intervalu.",
						"sk" => "Neplatný začiatok intervalu.",
						"en" => "Invalid interval start.",
					])))
						->addParam($startParam)
				);
			}
		}

		if (trim($endParam)) {
			$end = $timeClass::createFromString($endParam);
			if (!$end) {
				$result->addError(
					(new \Katu\Errors\Error("Neplatný konec intervalu.", "INVALID_INTERVAL_END", ErrorVersionCollection::createFromArray([
						"cs" => "Neplatný konec intervalu.",
						"sk" => "Neplatný koniec intervalu.",
						"en" => "Invalid interval end.",
					])))
						->addParam($endParam)
				);
			}
		}

		if ($start && !$end) {
			$end = clone $start;
		}

		if ($start && $end && $start > $end) {
			$result->addError(
				(new \Katu\Errors\Error("Začátek intervalu je později než jeho konec.", "INTERVAL_START_AFTER_END", ErrorVersionCollection::createFromArray([
					"cs" => "Začátek intervalu je později než jeho konec.",
					"sk" => "Začiatok intervalu je neskôr ako jeho koniec.",
					"en" => "Interval start is after its end.",
				])))
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

		if ($start <= $end) {
			return new $intervalClass($start, $end);
		}

		return null;
	}

	public function subtract(Interval $subtract): IntervalCollection
	{
		$intervalCollectionClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\IntervalCollection::class);

		var_dump($this);
		var_dump($subtract);
		die;
	}

	public function split(int $seconds): IntervalCollection
	{
		$intervalCollectionClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\IntervalCollection::class);
		$intervalClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Interval::class);

		$res = new $intervalCollectionClass;

		$currentStart = clone $this->getStart();
		$endTime = $this->getEnd();

		while ($currentStart < $endTime) {
			$currentEnd = clone $currentStart;
			$currentEnd = $currentEnd->modify("+ {$seconds} seconds");

			// Ensure we don't exceed the original interval end
			if ($currentEnd > $endTime) {
				$currentEnd = clone $endTime;
			}

			$res[] = new $intervalClass($currentStart, $currentEnd);

			// Move to next segment
			$currentStart = clone $currentEnd;
		}

		return $res;
	}

	public function fitsTime(Time $time, bool $includeEnd = true): bool
	{
		return $this->getStart() <= $time && (($includeEnd && $this->getEnd() >= $time) || $this->getEnd() > $time);
	}

	/****************************************************************************
	 * REST.
	 */
	public function getRestResponse(?ServerRequestInterface $request = null, ?OptionCollection $options = null): RestResponse
	{
		return new RestResponse([
			"start" => $this->getStart(),
			"end" => $this->getEnd(),
		]);
	}
}
