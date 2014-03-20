<?php

namespace Jabli\Types;

class LatLng {

	public $lat;
	public $lng;

	public function __construct($lat, $lng) {
		$this->lat = (float) $lat;
		$this->lng = (float) $lng;
	}

}
