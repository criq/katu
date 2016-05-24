<?php

namespace Katu\Types\Geo;

class TLng extends TCoordinate {

	static function isValid($value) {
		return $value >= -180 && $value <= 180;
	}

}
