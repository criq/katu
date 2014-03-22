<?php

namespace Jabli\Types\Geo;

class Lng {

	public $value;

	public function __construct($value) {
		if (!self::isValid($value)) {
			throw new Exception("Invalid longitude.");
		}

		$this->value = (float) $value;
	}

	static function isValid($value) {
		return $lat >= -180 && $lat <= 180;
	}

	public function getRad() {
		return deg2rad($this->value);
	}

}
