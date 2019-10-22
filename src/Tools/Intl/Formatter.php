<?php

namespace Katu\Tools\Intl;

class Formatter {

	static function getPreferredLocales() {
		$app = \Katu\App::get();
		$headers = $app->getContainer()->get('request')->getHeader('Accept-Language');

		return ($headers[0] ?? null) ? \Katu\Types\TLocale::getPreferredFromRequest($headers[0]) : [];
	}

	static function getPreferredLocale($locale = null) {
		if ($locale) {
			return $locale;
		}

		$preferredLocaleCollection = static::getPreferredLocales();
		if (isset($preferredLocaleCollection[0])) {
			return $preferredLocaleCollection[0];
		}

		return false;
	}

	static function getLocalNumber($locale, $number) {
		$numberFormatter = new \NumberFormatter(static::getPreferredLocale($locale), \NumberFormatter::DECIMAL);

		return $numberFormatter->format($number);
	}

	static function getLocalReadableNumber($locale, $number) {
		$numberFormatter = new \NumberFormatter(static::getPreferredLocale($locale), \NumberFormatter::DECIMAL);
		$numberFormatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 3);
		$numberFormatter->setAttribute(\NumberFormatter::DECIMAL_ALWAYS_SHOWN, false);

		if ($number >= 1) {
			$numberFormatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 2);
		}
		if ($number >= 10) {
			$numberFormatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 1);
		}
		if ($number >= 100) {
			$numberFormatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 0);
		}

		return $numberFormatter->format($number);
	}

	static function getLocalPercent($locale, $number) {
		$numberFormatter = new \NumberFormatter(static::getPreferredLocale($locale), \NumberFormatter::PERCENT);

		return $numberFormatter->format($number);
	}

	static function getLocalCurrency($locale, $number, $currency) {
		$numberFormatter = new \NumberFormatter(static::getPreferredLocale($locale), \NumberFormatter::CURRENCY);

		return $numberFormatter->formatCurrency($number, $currency);
	}

	static function getLocalWholeCurrency($locale, $number, $currency) {
		$numberFormatter = new \NumberFormatter(static::getPreferredLocale($locale), \NumberFormatter::CURRENCY);
		$numberFormatter->setTextAttribute(\NumberFormatter::CURRENCY_CODE, $currency);
		$numberFormatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 0);

		return $numberFormatter->format($number);
	}

}
