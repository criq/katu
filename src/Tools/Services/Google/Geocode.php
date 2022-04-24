<?php

namespace Katu\Tools\Services\Google;

use Katu\Tools\Calendar\Timeout;
use Katu\Types\TIdentifier;

class Geocode
{
	public static function geocode($language, $address, $components = []) : ?GeocodeAddress
	{
		$res = \Katu\Cache\General::get(new TIdentifier(__CLASS__, __FUNCTION__, __LINE__), new Timeout('1 day'), function ($language, $address, $components) {
			$componentArray = [];
			foreach ($components as $componentName => $componentValue) {
				$componentArray[] = implode(':', [$componentName, $componentValue]);
			}

			try {
				$apiKeys = \Katu\Config\Config::get('google', 'geocode', 'api', 'keys');
			} catch (\Katu\Exceptions\MissingConfigException $e) {
				try {
					$apiKeys = [\Katu\Config\Config::get('google', 'geocode', 'api', 'key')];
				} catch (\Katu\Exceptions\MissingConfigException $e) {
					$apiKeys = [\Katu\Config\Config::get('google', 'api', 'key')];
				}
			}

			foreach ($apiKeys as $apiKey) {
				$url = \Katu\Types\TURL::make('https://maps.googleapis.com/maps/api/geocode/json', [
					'address'    => $address,
					'components' => implode('|', $componentArray),
					'sensor'     => 'false',
					'language'   => $language,
					'key'        => $apiKey,
				]);

				$curl = new \Curl\Curl;
				$response = $curl->get((string) $url);

				if (isset($response->status) && $response->status == 'OVER_QUERY_LIMIT') {
					continue;
				}

				if (isset($response->status) && in_array($response->status, ['OK', 'ZERO_RESULTS'])) {
					return $response;
				}
			}

			throw new \Katu\Exceptions\DoNotCacheException(isset($response) ? $response : null);
		}, $language, $address, $components);

		if (!isset($res->results[0])) {
			return null;
		}

		return new \Katu\Tools\Services\Google\GeocodeAddress($language, $res->results[0]);
	}
}
