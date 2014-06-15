<?php

namespace Katu\Types;

class TAcceptedLocaleCollection {

	public $acceptedLocales = array();

	public function add(TAcceptedLocale $acceptedLocale) {
		$this->acceptedLocales[] = $acceptedLocale;
	}

	public function getPreferredLanguages() {
		return array_map(function($i) {
			return $i->locale->language;
		}, $this->acceptedLocales);
	}

	public function getLanguageFromOptions() {
		foreach (func_get_args() as $language) {
			if (in_array($language, $this->getPreferredLanguages())) {
				return $language;
			}
		}

		return FALSE;
	}

}
