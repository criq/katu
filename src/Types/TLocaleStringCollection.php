<?php

namespace Katu\Types;

class TLocaleStringCollection extends \ArrayObject
{
	public function add(TPreferredLocale $acceptedLocale)
	{
		$this->strings[] = $acceptedLocale;
	}

	public function getLocaleArray()
	{
		$array = new \Katu\Types\TArray;

		foreach ($this as $localeString) {
			$array[(string) $localeString->locale] = $localeString;
		}

		return $array;
	}

	public function getLanguageArray()
	{
		$array = new \Katu\Types\TArray;

		foreach ($this as $localeString) {
			$array[$localeString->locale->language] = $localeString;
		}

		return $array;
	}

	public function getPreferredString()
	{
		$preferredLocales = \Katu\Tools\Intl\Formatter::getPreferredLocales();

		// Exact match.
		$localeStringArray = $this->getLocaleArray();
		foreach ($preferredLocales as $preferredLocale) {
			if (isset($localeStringArray[(string) $preferredLocale])) {
				return $localeStringArray[(string) $preferredLocale];
			}
		}

		// Language match.
		$languageStringArray = $this->getLanguageArray();
		foreach ($preferredLocales as $preferredLocale) {
			if (isset($languageStringArray[(string) $preferredLocale->locale->language])) {
				return $languageStringArray[(string) $preferredLocale->locale->language];
			}
		}

		return false;
	}
}
