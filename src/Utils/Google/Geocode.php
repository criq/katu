<?php

namespace Katu\Utils\Google;

use \Katu\Config;
use \Katu\Utils\Cache;
use \Katu\Types\TUrl;

class Geocode {

	static function geocode($language, $address, $components = []) {
		$res = Cache::get(['geocode', $language, sha1($address), sha1(\Katu\Utils\JSON::encode($components))], function() use($language, $address, $components) {

			$componentArray = [];
			foreach ($components as $componentName => $componentValue) {
				$componentArray[] = implode(':', [$componentName, $componentValue]);
			}

			$url = TUrl::make('https://maps.googleapis.com/maps/api/geocode/json', [
				'address'    => $address,
				'components' => implode('|', $componentArray),
				'sensor'     => 'false',
				'language'   => $language,
				'key'        => Config::get('google', 'geocode', 'api', 'key'),
			]);

			$curl = new \Curl\Curl;
			$response = $curl->get((string) $url);

			if (!isset($response->status) || (isset($response->status) && $response->status != 'OK')) {
				throw new \Katu\Exceptions\DoNotCacheException($response);
			}

			return $response;

		});

		if (!isset($res->results[0])) {
			return false;
		}

		return new GeocodeAddress($language, $res->results[0]);
	}

}
