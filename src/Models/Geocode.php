<?php

namespace Katu\Models;

class Geocode extends \Katu\Model
{
	const TABLE = 'geocodes';

	public static function make($language, $address, $components = [], $extra = [])
	{
		return static::getOrCreateFromAddress(call_user_func_array('\Katu\Utils\Google\Geocode::geocode', [$language, $address, $components]), $extra);
	}

	public static function getOrCreateFromAddress($geocodeAddress, $extra = [])
	{
		if (!$geocodeAddress || !($geocodeAddress instanceof \Katu\Utils\Google\GeocodeAddress)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid geocode address.");
		}

		$hash = static::getHashByGeocodeAddress($geocodeAddress, $extra);

		$geocode = static::getOneBy([
			'hash' => $hash,
		]);
		if (!$geocode) {
			$params = [
				'timeCreated'  => (string) \Katu\Utils\DateTime::get()->getDbDateTimeFormat(),
				'hash'         => (string) $hash,
				'language'     => (string) $geocodeAddress->language,
				'number'       => (string) $geocodeAddress->number,
				'premise'      => (string) $geocodeAddress->premise,
				'street'       => (string) $geocodeAddress->street,
				'neighborhood' => (string) $geocodeAddress->neighborhood,
				'part'         => (string) $geocodeAddress->part,
				'city'         => (string) $geocodeAddress->city,
				'county'       => (string) $geocodeAddress->county,
				'district'     => (string) $geocodeAddress->district,
				'country'      => (string) $geocodeAddress->country,
				'zip'          => (string) $geocodeAddress->zip,
				'formatted'    => (string) $geocodeAddress->formatted,
				'lat'          => (float)  $geocodeAddress->latlng->lat->getDeg(),
				'lng'          => (float)  $geocodeAddress->latlng->lng->getDeg(),
				'latRad'       => (float)  $geocodeAddress->latlng->lat->getRad(),
				'lngRad'       => (float)  $geocodeAddress->latlng->lng->getRad(),
			];

			$params = array_merge($params, $extra);

			$geocode = static::insert($params);
		}

		return $geocode;
	}

	public static function getHashByGeocodeAddress($geocodeAddress, $extra = [])
	{
		if (!$geocodeAddress || !($geocodeAddress instanceof \Katu\Utils\Google\GeocodeAddress)) {
			throw new \Exception("Invalid geocode address.");
		}

		$params = [
			'language'     => (string) $geocodeAddress->language,
			'number'       => (string) $geocodeAddress->number,
			'premise'      => (string) $geocodeAddress->premise,
			'street'       => (string) $geocodeAddress->street,
			'neighborhood' => (string) $geocodeAddress->neighborhood,
			'part'         => (string) $geocodeAddress->part,
			'city'         => (string) $geocodeAddress->city,
			'county'       => (string) $geocodeAddress->county,
			'district'     => (string) $geocodeAddress->district,
			'country'      => (string) $geocodeAddress->country,
			'zip'          => (string) $geocodeAddress->zip,
			'formatted'    => (string) $geocodeAddress->formatted,
			'lat'          => (string) $geocodeAddress->latlng->lat,
			'lng'          => (string) $geocodeAddress->latlng->lng,
		];

		$params = array_merge($params, $extra);

		return static::getHashByArray($params);
	}

	public static function getHashByArray($array)
	{
		ksort($array);

		return sha1(json_encode($array));
	}

	public function hasPropertyAddress()
	{
		return ($this->number || $this->premise || $this->street);
	}
}
