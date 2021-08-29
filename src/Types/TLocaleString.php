<?php

namespace Katu\Types;

class TLocaleString {

	public $locale;
	public $string;

	public function __construct($locale, $string) {
		$this->locale = $locale;
		$this->string = $string;
	}

	public function __toString() {
		return (string) $this->string;
	}

}
