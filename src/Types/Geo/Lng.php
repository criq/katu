<?php

namespace Jabli\Types\Geo;

class Lng extends Coordinate {

	static function isValid($value) {
		return $value >= -180 && $value <= 180;
	}

}
