<?php

namespace Katu\Utils;

class DateTime extends \DateTime
{
	public function __toString() : string
	{
		return $this->getDbDateTimeFormat();
	}

	public static function get($string = null) : DateTime
	{
		if (is_int($string)) {
			return new DateTime('@' . $string);
		}

		return new DateTime($string);
	}

	public static function createFromTimestamp($timestamp) : DateTime
	{
		return new static('@' . $timestamp);
	}

	public static function createFromDateTime($dateTime) : ?DateTime
	{
		if ($dateTime) {
			return new static($dateTime->format('Y-m-d H:i:s'), $dateTime->getTimezone());
		} else {
			return null;
		}
	}

	public static function createFromFormat($format, $string, $dateTimeZone = null) : ?DateTime
	{
		if ($dateTimeZone) {
			return static::createFromDateTime(\DateTime::createFromFormat($format, $string, $dateTimeZone));
		} else {
			return static::createFromDateTime(\DateTime::createFromFormat($format, $string));
		}
	}

	public function change($change) : DateTime
	{
		$datetime = clone $this;
		$datetime->modify($change);

		return $datetime;
	}

	public function toLocalTimezone() : DateTime
	{
		return $this->setTimezone(new \DateTimeZone(\Katu\Config::get('app', 'timezone')));
	}

	public function getDbDateFormat() : string
	{
		return $this->format('Y-m-d');
	}

	public function getDbTimeFormat() : string
	{
		return $this->format('H:i:s');
	}

	public function getDbDateTimeFormat() : string
	{
		return $this->format('Y-m-d H:i:s');
	}

	public function isValid() : bool
	{
		return $this->getTimestamp() > 0;
	}

	public function isInTimeout($timeout) : bool
	{
		return ($this->getTimestamp() + $timeout) >= time();
	}

	public function isYesterday() : bool
	{
		return (new static('- 1 day', $this->getTimezone()))->format('Y-m-d') == $this->format('Y-m-d');
	}

	public function isToday() : bool
	{
		return (new static('now', $this->getTimezone()))->format('Y-m-d') == $this->format('Y-m-d');
	}

	public function isTomorrow() : bool
	{
		return (new static('+ 1 day', $this->getTimezone()))->format('Y-m-d') == $this->format('Y-m-d');
	}

	public function isInFuture() : bool
	{
		return $this->getTimestamp() > time();
	}

	public function isInPast() : bool
	{
		return $this->getTimestamp() < time();
	}

	public function isNow() : bool
	{
		return $this->getTimestamp() == time();
	}

	public function getAge() : int
	{
		return time() - $this->getTimestamp();
	}

	public static function getMicroseconds() : float
	{
		list($micro, $timestamp) = explode(' ', microtime(false));

		return (float)$micro;
	}

	public static function getMicrotime() : float
	{
		list($micro, $timestamp) = explode(' ', microtime(false));

		return (float)($timestamp + $micro);
	}

	public function getThisWeekday($weekday) : ?DateTime
	{
		$date = clone $this;

		$weekdays = [
			1 => ['Monday',    'monday',    'mon'],
			2 => ['Tuesday',   'tuesday',   'tue'],
			3 => ['Wednesday', 'wednesday', 'wed'],
			4 => ['Thursday',  'thursday',  'thu'],
			5 => ['Friday',    'friday',    'fri'],
			6 => ['Saturday',  'saturday',  'sat'],
			7 => ['Sunday',    'sunday',    'sun'],
		];

		$monday = $date->modify('- ' . ($date->format('N') - 1) . ' days');

		foreach ($weekdays as $position => $names) {
			if (in_array($weekday, $names)) {
				return $monday->modify('+ ' . ($position - 1) . ' days');
			}
		}

		return null;
	}

	public function getNextWeekday($weekday) : DateTime
	{
		$date = clone $this;

		return $date->modify($weekday);
	}

	public function setYear($n) : DateTime
	{
		return $this->setDate($n, $this->format('n'), $this->format('j'));
	}

	public function setMonth($n) : DateTime
	{
		return $this->setDate($this->format('Y'), $n, $this->format('j'));
	}

	public function setDay($n) : DateTime
	{
		return $this->setDate($this->format('Y'), $this->format('n'), $n);
	}

	public function setHour($n) : DateTime
	{
		return $this->setTime($n, $this->format('i'), $this->format('s'));
	}

	public function setMinute($n) : DateTime
	{
		return $this->setTime($this->format('H'), $n, $this->format('s'));
	}

	public function setSecond($n) : DateTime
	{
		return $this->setTime($this->format('H'), $this->format('i'), $n);
	}

	public function getDiff($dateTime = null) : \DateInterval
	{
		return $this->diff($dateTime ?: new static);
	}
}
