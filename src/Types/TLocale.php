<?php

namespace Katu\Types;

class TLocale {

	public $language;
	public $country;

	public function __construct() {
		if (count(func_get_args()) == 1) {
			@list($this->language, $this->country) = explode('_', func_get_arg(0));
		} elseif (count(func_get_args()) == 2) {
			$this->language = func_get_arg(0);
			$this->country = func_get_arg(1);
		}
	}

	public function __toString() {
		return implode('_', array_filter([
			$this->language,
			$this->country,
		]));
	}

	static function getPreferredFromRequest($requestHeader) {
		$locales = new \Katu\Types\TPreferredLocaleCollection();

		foreach (explode(',', $requestHeader) as $locale) {
			$locales->add(\Katu\Types\TPreferredLocale::getFromRequestHeader($locale));
		}

		return $locales;
	}

}
