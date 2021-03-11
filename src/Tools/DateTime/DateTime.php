<?php

namespace Katu\Tools\DateTime;

class DateTime extends \DateTime
{
	public function __construct($time = null, \DateTimeZone $timezone = null)
	{
		if ($time instanceof \DateTime) {
			$time = $time->format('r');
		}

		if (!$timezone) {
			$timezone = $this->getLocalTimeZone();
		}

		return parent::__construct($time, $timezone);
	}

	public function __toString() : string
	{
		return $this->getDbDateTimeFormat();
	}

	public static function createFromTimestamp(int $timestamp) : DateTime
	{
		return new static('@' . $timestamp);
	}

	public static function createFromDateTime(\DateTime $dateTime) : DateTime
	{
		return new static($dateTime->format('Y-m-d H:i:s'), $dateTime->getTimezone());
	}

	public static function createFromString(?string $string) : ?DateTime
	{
		if (!trim($string)) {
			return null;
		}

		if ($string == '0000-00-00' || $string == '0000-00-00 00:00:00') {
			return null;
		}

		try {
			$datetime = new static($string);
			if ($datetime->format('Y') < 0) {
				return null;
			}

			return $datetime;
		} catch (\Throwable $e) {
			return null;
		}

		return null;
	}

	public function getLocalTimeZone() : \DateTimeZone
	{
		return new \DateTimeZone(\Katu\Config\Config::get('app', 'timezone'));
	}

	public static function get($time = null, \DateTimeZone $timezone = null)
	{
		if (is_int($time)) {
			return new static('@' . $time, $timezone);
		}

		return new static($time, $timezone);
	}

	public function toLocalTimezone()
	{
		return $this->setTimezone($this->getLocalTimeZone());
	}

	public function getDbDateFormat()
	{
		return $this->format('Y-m-d');
	}

	public function getDbTimeFormat()
	{
		return $this->format('H:i:s');
	}

	public function getDbDateTimeFormat()
	{
		return $this->format('Y-m-d H:i:s');
	}

	public function isValid()
	{
		return $this->getTimestamp() > 0;
	}

	public function isInTimeout($timeout)
	{
		return ($this->getTimestamp() + $timeout) >= time();
	}

	public function isYesterday()
	{
		return (new static('- 1 day', $this->getTimezone()))->format('Y-m-d') == $this->format('Y-m-d');
	}

	public function isToday()
	{
		return (new static('now', $this->getTimezone()))->format('Y-m-d') == $this->format('Y-m-d');
	}

	public function isTomorrow()
	{
		return (new static('+ 1 day', $this->getTimezone()))->format('Y-m-d') == $this->format('Y-m-d');
	}

	public function isInFuture()
	{
		return $this->getTimestamp() > time();
	}

	public function isInPast()
	{
		return $this->getTimestamp() < time();
	}

	public function isNow()
	{
		return $this->getTimestamp() == time();
	}

	public function getAge()
	{
		return time() - $this->getTimestamp();
	}

	public static function getMicroseconds()
	{
		list($micro, $timestamp) = explode(' ', microtime(false));

		return (float) $micro;
	}

	public static function getMicrotime()
	{
		list($micro, $timestamp) = explode(' ', microtime(false));

		return (float) ($timestamp + $micro);
	}

	public function getThisWeekday($weekday)
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

		return false;
	}

	public function getNextWeekday($weekday)
	{
		$date = clone $this;

		return $date->modify($weekday);
	}

	public function setYear($n)
	{
		return $this->setDate($n, $this->format('n'), $this->format('j'));
	}

	public function setMonth($n)
	{
		return $this->setDate($this->format('Y'), $n, $this->format('j'));
	}

	public function setDay($n)
	{
		return $this->setDate($this->format('Y'), $this->format('n'), $n);
	}

	public function setHour($n)
	{
		return $this->setTime($n, $this->format('i'), $this->format('s'));
	}

	public function setMinute($n)
	{
		return $this->setTime($this->format('H'), $n, $this->format('s'));
	}

	public function setSecond($n)
	{
		return $this->setTime($this->format('H'), $this->format('i'), $n);
	}

	public function getDiff($dateTime = null)
	{
		return $this->diff($dateTime ?: new static);
	}
}
