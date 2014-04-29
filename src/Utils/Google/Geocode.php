<?php

namespace Katu\Utils\Google;

use \Katu\Config;
use \Katu\Utils\Cache;
use \Katu\Types\TURL;

class Geocode {

	static function geocode($address, $language = 'en') {
		$arr = Cache::getURL(TURL::make('https://maps.googleapis.com/maps/api/geocode/json', array(
			'address'  => $address,
			'sensor'   => 'false',
			'language' => $language,
			'key'      => Config::get('google', 'geocode', 'apiKey'),
		)));

		if (!isset($arr['results'][0])) {
			return FALSE;
		}

		return new GeocodeAddress($language, $arr['results'][0]);
	}

}
