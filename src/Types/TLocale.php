<?php

namespace Katu\Types;

class TLocale {

	public $language   = null;
	public $country    = null;

	public function __construct($language = null, $country = null) {
		$this->language   = (string) $language;
		$this->country    = (string) $country;
	}

}
