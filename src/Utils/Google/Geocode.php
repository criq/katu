<?php

namespace Katu\Utils\Google;

use \Katu\Config,
    \Katu\Utils\JSON,
    \Katu\Utils\Cache,
    \Katu\Types\TURL;

class Geocode {

	static function geocode($address, $language = 'en') {
		$arr = Cache::getURL(TURL::make('https://maps.googleapis.com/maps/api/geocode/json', array(
			'address'  => $address,
			'sensor'   => 'false',
			'language' => $language,
			'key'      => Config::get('google', 'apiKey'),
		)));

		if (!isset($arr['results'][0])) {
			return FALSE;
		}

		return new GeocodeAddress($arr['results'][0]);
	}

}
