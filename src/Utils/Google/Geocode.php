<?php

namespace Jabli\Utils\Google;

use \Jabli\Config,
    \Jabli\Utils\JSON,
    \Jabli\Utils\Cache,
    \Jabli\Utils\URL;

class Geocode {

	static function geocode($address) {
		$arr = JSON::decodeAsArray(Cache::getURL(URL::make('https://maps.googleapis.com/maps/api/geocode/json', array(
			'address'  => $address,
			'sensor'   => 'false',
			'language' => 'cs',
			'key'      => Config::getApp('google', 'api_key'),
		))));

		if (!isset($arr['results'][0])) {
			return FALSE;
		}

		return new GeocodeAddress($arr['results'][0]);
	}

	static function getByLatLng($lat, $lng) {
		return self::geocode(implode(',', array($lat, $lng)));
	}

	static function getByAddress() {
		return self::geocode(implode(',', func_get_args()));
	}

}
