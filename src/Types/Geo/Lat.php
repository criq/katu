<?php

namespace Jabli\Types\Geo;

class Lat {

	public $value;

	public function __construct($value) {
		if (!self::isValid($value)) {
			throw new Exception("Invalid latitude.");
		}

		$this->value = (float) $value;
	}

	static function isValid($value) {
		return $lat >= -90 && $lat <= 90;
	}

	public function getRad() {
		return deg2rad($this->value);
	}

}
