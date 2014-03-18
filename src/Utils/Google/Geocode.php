<?php

namespace Jabli\Utils\Google;

use \Jabli\Config,
    \Jabli\Utils\JSON,
    \Jabli\Utils\Cache,
    \Jabli\Utils\URL;

class Geocode {

	static function getByLatLng($lat, $lng) {
		return JSON::decodeAsArray(Cache::getURL(URL::make('https://maps.googleapis.com/maps/api/geocode/json', array(
			'address' => implode(',', array($lat, $lng)),
			'sensor'  => 'false',
			'key'     => Config::get('google', 'api_key'),
		))));
	}

}
