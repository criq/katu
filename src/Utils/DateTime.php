<?php

namespace Katu\Utils;

class DateTime extends \DateTime {

	static function get($string = NULL) {
		if (is_int($string)) {
			return new DateTime('@' . $string);
		}

		return new DateTime($string);
	}

	public function toLocalTimezone() {
		return $this->setTimezone(new \DateTimeZone(\Katu\Config::get('app', 'timezone')));
	}

	public function getDBDateFormat() {
		return $this->format('Y-m-d');
	}

	public function getDBDateTimeFormat() {
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

}
