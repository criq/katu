<?php

namespace Katu\Utils;

class DateTime extends \DateTime {

	static function get($string = NULL) {
		if (is_int($string)) {
			return new DateTime('@' . $string);
		}

		return new DateTime($string);
	}

	static function createFromDateTime($dateTime) {
		return new static('@' . $dateTime->getTimestamp(), $dateTime->getTimezone());
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

}
