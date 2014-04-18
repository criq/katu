<?php

namespace Katu\Types\Geo;

class TLat extends Coordinate {

	static function isValid($value) {
		return $value >= -90 && $value <= 90;
	}

}
