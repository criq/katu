<?php

namespace Katu\Tools\Calendar;

class Time extends \DateTime
{
	public function __construct($time = null, \DateTimeZone $timezone = null)
	{
		if ($time instanceof \DateTime) {
			$time = $time->format("r");
		}

		if (!$timezone) {
			$timezone = $this->getLocalTimeZone();
		}

		return parent::__construct($time, $timezone);
	}

	public function __toString(): string
	{
		return $this->getDbDateTimeFormat();
	}

	public static function createFromTimestamp(int $timestamp): Time
	{
		$timeClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Time::class);

		return new $timeClass("@{$timestamp}");
	}

	public static function createFromDateTime(\DateTime $dateTime): Time
	{
		$timeClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Time::class);

		return new $timeClass($dateTime->format("Y-m-d H:i:s"), $dateTime->getTimezone());
	}

	public static function createFromString(?string $string, bool $timeRequired): ?Time
	{
		$timeClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Time::class);

		$string = trim($string);

		$time = null;

		if ($timeClass::createFromFormat("Y-m-d\TH:i:sP", $string)) { // 2022-04-01T13:00:00+02:00
			$time = new $timeClass($string);
		} elseif ($timeClass::createFromFormat("Y-m-d\TH:i:sp", $string)) { // 2022-04-01T13:00:00Z
			$time = new $timeClass($string);
		} elseif ($timeClass::createFromFormat("Y-m-d\TH:i:s.vP", $string)) { // 2022-04-01T13:00:00.000+02:00
			$time = new $timeClass($string);
		} elseif ($timeClass::createFromFormat("Y-m-d\TH:i:s.vp", $string)) { // 2022-04-01T13:00:00.000Z
			$time = new $timeClass($string);
		} elseif ($timeClass::createFromFormat("D, j M Y H:i:s O", $string)) { // Thu, 21 Dec 2000 16:01:07 +0200
			$time = new $timeClass($string);
		}

		if (!($time ?? null)) {
			$time =
				$timeClass::createFromFormat("!Y-m-d H:i:s", $string)
				?: $timeClass::createFromFormat("!Y-m-d H:i", $string)
				?: $timeClass::createFromFormat("!Y-m-d H.i", $string)
				?: $timeClass::createFromFormat("!Y-m-d H", $string)
				?: (!$timeRequired ? $timeClass::createFromFormat("!Y-m-d", $string) : null)
				?: $timeClass::createFromFormat("!j.n.Y H:i:s", $string)
				?: $timeClass::createFromFormat("!j. n. Y H:i:s", $string)
				?: $timeClass::createFromFormat("!j.n. Y H:i:s", $string)
				?: $timeClass::createFromFormat("!j. n.Y H:i:s", $string)
				?: $timeClass::createFromFormat("!j.n.Y H:i", $string)
				?: $timeClass::createFromFormat("!j. n. Y H:i", $string)
				?: $timeClass::createFromFormat("!j.n. Y H:i", $string)
				?: $timeClass::createFromFormat("!j. n.Y H:i", $string)
				?: $timeClass::createFromFormat("!j.n.Y H.i", $string)
				?: $timeClass::createFromFormat("!j. n. Y H.i", $string)
				?: $timeClass::createFromFormat("!j.n. Y H.i", $string)
				?: $timeClass::createFromFormat("!j. n.Y H.i", $string)
				?: $timeClass::createFromFormat("!j.n.Y H", $string)
				?: $timeClass::createFromFormat("!j. n. Y H", $string)
				?: $timeClass::createFromFormat("!j.n. Y H", $string)
				?: $timeClass::createFromFormat("!j. n.Y H", $string)
				?: (!$timeRequired ? $timeClass::createFromFormat("!j.n.Y", $string) : null)
				;
		}

		if (!$time) {
			$y = "(?<y>([0-9]{2})|([0-9]{4}))";
			$m = "(?<m>(0?[1-9])|(1[0-2]))";
			$d = "(?<d>(0?[1-9])|([12][0-9])|(3[01]))";
			$h = "(?<h>(0?[0-9])|(1[0-9])|(2[0-3]))";
			$i = "(?<i>(0[0-9])|([1-5][0-9]))";
			$s = "(?<s>(0[0-9])|([1-5][0-9]))";

			$regexps = array_values(array_filter([
				"/^$y-$m-$d $h:$i:$s$/",
				!$timeRequired ? "/^$y-$m-$d$/" : null,
				!$timeRequired ? "/^$d.\s*$m.\s*$y$/" : null,
			]));

			foreach ($regexps as $regexp) {
				if (preg_match($regexp, $string, $match)) {
					$time = (new $timeClass)
						->setDate(
							$match["y"] ?? null,
							$match["m"] ?? null,
							$match["d"] ?? null,
						)
						->setTime(
							$match["h"] ?? null,
							$match["i"] ?? null,
							$match["s"] ?? null,
						)
						;
					break;
				}
			}
		}

		return $time ? new $timeClass($time) : null;
	}

	public function getLocalTimeZone(): \DateTimeZone
	{
		return new \DateTimeZone(\Katu\Config\Config::get("app", "timezone"));
	}

	public function toLocalTimezone(): Time
	{
		return $this->setTimezone($this->getLocalTimeZone());
	}

	public function getDbDateFormat(): string
	{
		return $this->format("Y-m-d");
	}

	public function getDbTimeFormat(): string
	{
		return $this->format("H:i:s");
	}

	public function getDbDateTimeFormat(): string
	{
		return $this->format("Y-m-d H:i:s");
	}

	public function isValid(): bool
	{
		return $this->getTimestamp() > 0;
	}

	public function fitsInTimeout(Timeout $timeout): bool
	{
		return $timeout->fits($this);
	}

	public function isYesterday(): bool
	{
		$timeClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Time::class);

		return (new $timeClass("- 1 day", $this->getTimezone()))->format("Y-m-d") == $this->format("Y-m-d");
	}

	public function isToday(): bool
	{
		$timeClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Time::class);

		return (new $timeClass("now", $this->getTimezone()))->format("Y-m-d") == $this->format("Y-m-d");
	}

	public function isTomorrow(): bool
	{
		$timeClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Time::class);

		return (new $timeClass("+ 1 day", $this->getTimezone()))->format("Y-m-d") == $this->format("Y-m-d");
	}

	public function isInFuture(): bool
	{
		return $this->getTimestamp() > time();
	}

	public function isInPast(): bool
	{
		return $this->getTimestamp() < time();
	}

	public function isNow(): bool
	{
		return $this->getTimestamp() == time();
	}

	public function getAge(): Seconds
	{
		return new Seconds($this->getTimestamp() - time());
	}

	public static function getMicroseconds(): float
	{
		list($micro, $timestamp) = explode(" ", microtime(false));

		return (float)$micro;
	}

	public static function getMicrotime(): float
	{
		list($micro, $timestamp) = explode(" ", microtime(false));

		return (float)($timestamp + $micro);
	}

	public function getThisWeekday(string $weekday): Day
	{
		$dayClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Day::class);

		$date = clone $this;

		$weekdays = [
			1 => ["Monday",    "monday",    "mon"],
			2 => ["Tuesday",   "tuesday",   "tue"],
			3 => ["Wednesday", "wednesday", "wed"],
			4 => ["Thursday",  "thursday",  "thu"],
			5 => ["Friday",    "friday",    "fri"],
			6 => ["Saturday",  "saturday",  "sat"],
			7 => ["Sunday",    "sunday",    "sun"],
		];

		$monday = $date->modify("- " . ($date->format("N") - 1) . " days");

		foreach ($weekdays as $position => $names) {
			if (in_array($weekday, $names)) {
				return new $dayClass($monday->modify("+ " . ($position - 1) . " days"));
			}
		}

		return null;
	}

	public function getNextWeekday($weekday): Time
	{
		$date = clone $this;

		return $date->modify($weekday);
	}

	public function setYear(int $n)
	{
		return $this->setDate($n, $this->format("n"), $this->format("j"));
	}

	public function setMonth(int $n)
	{
		return $this->setDate($this->format("Y"), $n, $this->format("j"));
	}

	public function setDay(int $n)
	{
		return $this->setDate($this->format("Y"), $this->format("n"), $n);
	}

	public function setHour(int $n)
	{
		return $this->setTime($n, $this->format("i"), $this->format("s"), $this->format("u"));
	}

	public function setMinute(int $n)
	{
		return $this->setTime($this->format("H"), $n, $this->format("s"), $this->format("u"));
	}

	public function setSecond(float $n)
	{
		return $this->setTime($this->format("H"), $this->format("i"), floor($n), ($n - floor($n)) * 1000000);
	}

	public function setMicrosecond(int $n)
	{
		return $this->setTime($this->format("H"), $this->format("i"), $this->format("s"), $n);
	}

	public function getDiff($dateTime = null)
	{
		$timeClass = \App\App::getContainer()->get(\Katu\Tools\Calendar\Time::class);

		return $this->diff($dateTime ?: new $timeClass);
	}

	public function change($value): Time
	{
		return (clone $this)->modify($value);
	}
}
