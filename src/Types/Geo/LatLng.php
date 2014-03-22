<?php

namespace Jabli\Types\Geo;

class LatLng {

	public $lat;
	public $lng;

	public function __construct($lat, $lng) {
		$this->lat = new Lat($lat);
		$this->lng = new Lng($lng);
	}

}
