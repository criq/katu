<?php

namespace Katu\Types\Geo;

class Lat extends Coordinate {

	static function isValid($value) {
		return $value >= -90 && $value <= 90;
	}

}
