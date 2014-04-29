<?php

namespace Katu\Models;

use \Katu\Exception;

class Geocode extends \Katu\Model {

	const TABLE = 'geocodes';

	static function getOrCreateFromAddress($geocodeAddress) {
		if (!$geocodeAddress || !($geocodeAddress instanceof \Katu\Utils\Google\GeocodeAddress)) {
			throw new Exception("Invalid geocode address.");
		}

		$hash = self::getHashByGeocodeAddress($geocodeAddress);

		$geocode = static::get('hash', $hash)->getOne();
		if (!$geocode) {
			$geocode = self::insert(array(
				'timeCreated'  => (string) \Katu\Utils\DateTime::get()->getDBDatetimeFormat(),
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
				'lat'          => (string) $geocodeAddress->latlng->lat,
				'lng'          => (string) $geocodeAddress->latlng->lng,
			));
		}

		return $geocode;
	}

	static function getHashByGeocodeAddress($geocodeAddress) {
		if (!$geocodeAddress || !($geocodeAddress instanceof \Katu\Utils\Google\GeocodeAddress)) {
			throw new Exception("Invalid geocode address.");
		}

		return static::getHashByArray(array(
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
		));
	}

	static function getHashByArray($array) {
		ksort($array);

		return sha1(json_encode($array));
	}

}
