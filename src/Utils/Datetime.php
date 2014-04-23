<?php

namespace Katu\Utils;

class DateTime extends \DateTime {

	static function get($string = NULL) {
		if (is_int($string)) {
			return new DateTime('@' . $string);
		}

		return new DateTime($string);
	}

	public function getDBDateFormat() {
		return $this->format('Y-m-d');
	}

	public function getDBDatetimeFormat() {
		return $this->format('Y-m-d H:i:s');
	}

	public function isInTimeout($timeout) {
		return ($this->getTimestamp() + $timeout) >= time();
	}

	public function isInFuture() {
		return $this->getTimestamp() > time();
	}

	public function isInPast() {
		return $this->getTimestamp() < time();
	}

}
