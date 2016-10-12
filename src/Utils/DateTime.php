<?php

namespace Katu\Utils;

class DateTime extends \DateTime {

	public function __toString() {
		return $this->getDbDateTimeFormat();
	}

	static function get($string = null) {
		if (is_int($string)) {
			return new DateTime('@' . $string);
		}

		return new DateTime($string);
	}

	static function createFromTimestamp($timestamp) {
		return new static('@' . $timestamp);
	}

	static function createFromDateTime($dateTime) {
		if ($dateTime) {
			return new static($dateTime->format('Y-m-d H:i:s'), $dateTime->getTimezone());
		} else {
			return false;
		}
	}

	static function createFromFormat($format, $string, $dateTimeZone = null) {
		if ($dateTimeZone) {
			return static::createFromDateTime(\DateTime::createFromFormat($format, $string, $dateTimeZone));
		} else {
			return static::createFromDateTime(\DateTime::createFromFormat($format, $string));
		}
	}

	public function toLocalTimezone() {
		return $this->setTimezone(new \DateTimeZone(\Katu\Config::get('app', 'timezone')));
	}

	public function getDbDateFormat() {
		return $this->format('Y-m-d');
	}

	public function getDbDateTimeFormat() {
		return $this->format('Y-m-d H:i:s');
	}

	public function isValid() {
		return $this->getTimestamp() > 0;
	}

	public function isInTimeout($timeout) {
		return ($this->getTimestamp() + $timeout) >= time();
	}

	public function isYesterday() {
		return (new static('- 1 day', $this->getTimezone()))->format('Y-m-d') == $this->format('Y-m-d');
	}

	public function isToday() {
		return (new static('now', $this->getTimezone()))->format('Y-m-d') == $this->format('Y-m-d');
	}

	public function isTomorrow() {
		return (new static('+ 1 day', $this->getTimezone()))->format('Y-m-d') == $this->format('Y-m-d');
	}

	public function isInFuture() {
		return $this->getTimestamp() > time();
	}

	public function isInPast() {
		return $this->getTimestamp() < time();
	}

	public function isNow() {
		return $this->getTimestamp() == time();
	}

	public function getAge() {
		return time() - $this->getTimestamp();
	}

	static function getMicroseconds() {
		list($micro, $timestamp) = explode(' ', microtime(false));

		return (float) $micro;
	}

	static function getMicrotime() {
		list($micro, $timestamp) = explode(' ', microtime(false));

		return (float) ($timestamp + $micro);
	}

	public function getThisWeekday($weekday) {
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

	public function getNextWeekday($weekday) {
		$date = clone $this;

		return $date->modify($weekday);
	}

	public function setYear($n) {
		return $this->setDate($n, $this->format('n'), $this->format('j'));
	}

	public function setMonth($n) {
		return $this->setDate($this->format('Y'), $n, $this->format('j'));
	}

	public function setDay($n) {
		return $this->setDate($this->format('Y'), $this->format('n'), $n);
	}

}
