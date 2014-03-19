<?php

namespace Jabli\Utils\Google;

class GeocodeAddress {

	public $number;
	public $street;
	public $neighborhood;
	public $part;
	public $city;
	public $county;
	public $district;
	public $country;
	public $zip;
	public $formatted;
	public $lat;
	public $lng;

	static $mapping = array(
		'street_number' => 'number',
		'route' => 'street',
		'neighborhood' => 'neighborhood',
		'sublocality' => 'part',
		'locality' => 'city',
		'administrative_area_level_2' => 'county',
		'administrative_area_level_1' => 'district',
		'country' => 'country',
		'postal_code' => 'zip',
	);

	public function __construct($geo) {
		foreach ($geo['address_components'] as $component) {
			foreach ($component['types'] as $type) {
				if (isset(self::$mapping[$type])) {
					$this->{self::$mapping[$type]} = $component['long_name'];
				}
			}
		}

		$this->formatted = $geo['formatted_address'];

		$this->lat = $geo['geometry']['location']['lat'];
		$this->lng = $geo['geometry']['location']['lng'];
	}

}
