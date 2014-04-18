<?php

namespace Katu\Types\Geo;

class TCoordinate {

	public $value;

	public function __construct($value) {
		if (!static::isValid($value)) {
			throw new Exception("Invalid coordinate.");
		}

		$this->value = (float) $value;
	}

	public function __toString() {
		return (string) $this->value;
	}

	public function getDeg() {
		return $this->value;
	}

	public function getRad() {
		return deg2rad($this->value);
	}

}
