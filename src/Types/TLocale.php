<?php

namespace Katu\Types;

class TLocale {

	public $language   = NULL;
	public $country    = NULL;

	public function __construct($language = NULL, $country = NULL) {
		$this->language   = (string) $language;
		$this->country    = (string) $country;
	}

}
