<?php

namespace Katu\Utils;

class Locale {

	static function getAcceptedFromRequest($requestHeader) {
		$locales = new \Katu\Types\TAcceptedLocaleCollection();

		foreach (explode(',', $requestHeader) as $locale) {
			$locales->add(\Katu\Types\TAcceptedLocale::getFromRequestHeader($locale));
		}

		return $locales;
	}

}
