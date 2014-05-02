<?php

namespace Katu\Utils\Google;

use \Katu\Config;
use \Katu\Utils\Cache;
use \Katu\Types\TURL;

class Geocode {

	static function geocode($address, $language = 'en') {
		$res = Cache::get(array('geocode', $language, sha1($address)), function() use($address, $language) {

			$url = TURL::make('https://maps.googleapis.com/maps/api/geocode/json', array(
				'address'  => $address,
				'sensor'   => 'false',
				'language' => $language,
				'key'      => Config::get('google', 'geocode', 'apiKey'),
			));

			$curl = new \Curl;
			$curl->get((string) $url);

			return $curl->response;

		});

		if (!isset($res->results[0])) {
			return FALSE;
		}

		return new GeocodeAddress($language, $res->results[0]);
	}

}
