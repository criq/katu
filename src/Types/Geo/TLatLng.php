<?php

namespace Katu\Types\Geo;

class TLatLng {

	public $lat;
	public $lng;

	public function __construct($lat, $lng) {
		$this->lat = new TLat($lat);
		$this->lng = new TLng($lng);
	}

}
