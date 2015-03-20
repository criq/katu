<?php

namespace Katu\Utils;

class DateTime extends \DateTime {

	static function get($string = null) {
		if (is_int($string)) {
			return new DateTime('@' . $string);
		}

		return new DateTime($string);
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
		return $this->getTimestamp() >= 0;
	}

	public function isInTimeout($timeout) {
		return ($this->getTimestamp() + $timeout) >= time();
	}

	public function isToday() {
		return (new static('now', $this->getTimezone()))->format('Y-m-d') == $this->format('Y-m-d');
	}

	public function isInFuture() {
		return $this->getTimestamp() > time();
	}

	public function isInPast() {
		return $this->getTimestamp() < time();
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

	public function getClosestWeekday($dayNumber) {
		$dateTime = clone $this;

		while ($dateTime->format('N') != $dayNumber) {
			$dateTime->modify('+ 1 day');
		}

		return $dateTime;
	}

}
