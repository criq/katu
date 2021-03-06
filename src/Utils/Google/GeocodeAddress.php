<?php

namespace Katu\Utils\Google;

class GeocodeAddress {

	public $language;
	public $number;
	public $premise;
	public $street;
	public $neighborhood;
	public $part;
	public $city;
	public $county;
	public $district;
	public $country;
	public $zip;
	public $formatted;
	public $latlng;

	static $mapping = array(
		'street_number'               => 'number',
		'premise'                     => 'premise',
		'route'                       => 'street',
		'neighborhood'                => 'neighborhood',
		'sublocality'                 => 'part',
		'locality'                    => 'city',
		'administrative_area_level_2' => 'county',
		'administrative_area_level_1' => 'district',
		'country'                     => 'country',
		'postal_code'                 => 'zip',
	);

	public function __construct($language, $geo) {
		$this->language = $language;

		foreach ($geo->address_components as $component) {
			foreach ($component->types as $type) {
				if (isset(self::$mapping[$type])) {
					$this->{self::$mapping[$type]} = $component->long_name;
				}
			}
		}

		$this->formatted = $geo->formatted_address;

		$this->latlng = new \Katu\Types\Geo\TLatLng($geo->geometry->location->lat, $geo->geometry->location->lng);
	}

	public function isStreetLevel() {
		return !is_null($this->number) && !is_null($this->street);
	}

}
