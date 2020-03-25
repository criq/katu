<?php

namespace Katu\Models\Presets;

use \Sexy\Sexy as SX;

class Geocode extends \Katu\Models\Model
{
	const TABLE = 'geocodes';

	public static function make(string $language, string $address, array $components = [], array $extra = []) : Geocode
	{
		$geocodeAddress = \Katu\Tools\Services\Google\Geocode::geocode($language, $address, $components);

		return static::getOrCreateFromAddress($geocodeAddress, $extra);
	}

	public static function getOrCreateFromAddress(\Katu\Tools\Services\Google\GeocodeAddress $geocodeAddress, array $extra = []) : Geocode
	{
		$hash = static::getHashByGeocodeAddress($geocodeAddress, $extra);

		$geocode = static::getOneBy([
			'hash' => $hash,
		]);
		if (!$geocode) {
			$params = [
				'timeCreated'  => (string) \Katu\Tools\DateTime\DateTime::get()->getDbDateTimeFormat(),
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

	public static function getHashByGeocodeAddress(\Katu\Tools\Services\Google\GeocodeAddress $geocodeAddress, array $extra = []) : string
	{
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

	public static function getHashByArray(array $array) : string
	{
		ksort($array);

		return sha1(json_encode($array));
	}

	public function hasPropertyAddress() : bool
	{
		return ($this->number || $this->premise || $this->street);
	}

	public function getLatLng()
	{
		return new \Katu\Types\Geo\TLatLng($this->lat, $this->lng);
	}

	public static function getDistanceSqlSelectExpression(\Katu\Types\Geo\TLatLng $latLng)
	{
		return SX::calcMultiply([
			SX::val(6371),
			SX::fnAcos([
				SX::calcPlus([
					SX::calcMultiply([
						SX::fnCos([
							SX::val($latLng->lat->getRad()),
						]),
						SX::fnCos([
							static::getColumn('latRad'),
						]),
						SX::fnCos([
							SX::calcMinus([
								static::getColumn('lngRad'),
								SX::val($latLng->lng->getRad()),
							]),
						]),
					]),
					SX::calcMultiply([
						SX::fnSin([
							SX::val($latLng->lat->getRad()),
						]),
						SX::fnSin([
							static::getColumn('latRad'),
						]),
					]),
				]),
			]),
		]);
	}
}
