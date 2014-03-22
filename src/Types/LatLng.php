<?php

namespace Jabli\Types;

class LatLng {

	public $lat;
	public $lng;

	public function __construct($lat, $lng) {
		if ($lat < -90 || $lat > 90) {
			throw new Exception("Invalid latitude.");
		}
		if ($lng < -180 || $lng > 180) {
			throw new Exception("Invalid longitude.");
		}

		$this->lat = (float) $lat;
		$this->lng = (float) $lng;
	}

	public function getRadLat() {
		return deg2rad($this->lat);
	}

	public function getRadLng() {
		return deg2rad($this->lng);
	}

}
