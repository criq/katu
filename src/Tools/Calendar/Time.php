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
		return new static("@{$timestamp}");
	}

	public static function createFromDateTime(\DateTime $dateTime): Time
	{
		return new static($dateTime->format("Y-m-d H:i:s"), $dateTime->getTimezone());
	}

	public static function createFromString(?string $string): ?Time
	{
		if (!trim($string)) {
			return null;
		}

		if ($string == "0000-00-00" || $string == "0000-00-00 00:00:00") {
			return null;
		}

		try {
			$datetime = new static($string);
			if ($datetime->format("Y") < 0) {
				return null;
			}

			return $datetime;
		} catch (\Throwable $e) {
			return null;
		}

		return null;
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
		return (new static("- 1 day", $this->getTimezone()))->format("Y-m-d") == $this->format("Y-m-d");
	}

	public function isToday(): bool
	{
		return (new static("now", $this->getTimezone()))->format("Y-m-d") == $this->format("Y-m-d");
	}

	public function isTomorrow(): bool
	{
		return (new static("+ 1 day", $this->getTimezone()))->format("Y-m-d") == $this->format("Y-m-d");
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

	public function getThisWeekday(string $weekday): Time
	{
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
				return $monday->modify("+ " . ($position - 1) . " days");
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
		return $this->diff($dateTime ?: new static);
	}

	public function change($value): Time
	{
		return (clone $this)->modify($value);
	}
}