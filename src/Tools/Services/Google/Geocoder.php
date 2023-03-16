<?php

namespace Katu\Tools\Services\Google;

use Katu\Tools\Calendar\Timeout;
use Katu\Types\TIdentifier;

class Geocoder
{
	protected $address;
	protected $components;
	protected $language;

	public function __construct(string $language, string $address, ?array $components = [])
	{
		$this->address = $address;
		$this->components = $components;
		$this->language = $language;
	}

	public function getGeocodedAddress() : ?GeocodedAddress
	{
		$res = \Katu\Cache\General::get(new TIdentifier(__CLASS__, __FUNCTION__, $this), new Timeout("1 day"), function () {
			$componentArray = [];
			foreach ($this->components as $componentName => $componentValue) {
				$componentArray[] = implode(":", [$componentName, $componentValue]);
			}

			try {
				$apiKeys = \Katu\Config\Config::get("google", "geocode", "api", "keys");
			} catch (\Katu\Exceptions\MissingConfigException $e) {
				try {
					$apiKeys = [\Katu\Config\Config::get("google", "geocode", "api", "key")];
				} catch (\Katu\Exceptions\MissingConfigException $e) {
					$apiKeys = [\Katu\Config\Config::get("google", "api", "key")];
				}
			}

			foreach ($apiKeys as $apiKey) {
				$url = \Katu\Types\TURL::make("https://maps.googleapis.com/maps/api/geocode/json", [
					"address"    => $this->address,
					"components" => implode("|", $componentArray),
					"sensor"     => "false",
					"language"   => $this->language,
					"key"        => $apiKey,
				]);

				$curl = new \Curl\Curl;
				$response = $curl->get((string) $url);

				if (isset($response->status) && $response->status == "OVER_QUERY_LIMIT") {
					continue;
				}

				if (isset($response->status) && in_array($response->status, ["OK", "ZERO_RESULTS"])) {
					return $response;
				}
			}

			throw new \Katu\Exceptions\DoNotCacheException(isset($response) ? $response : null);
		});

		if (!isset($res->results[0])) {
			return null;
		}

		return new GeocodedAddress($this->language, $res->results[0]);
	}
}
