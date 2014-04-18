<?php

namespace Katu\Types\Geo;

class TLng extends Coordinate {

	static function isValid($value) {
		return $value >= -180 && $value <= 180;
	}

}
