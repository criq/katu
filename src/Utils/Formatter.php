<?php

namespace Katu\Utils;

class Formatter
{
	public static function getPreferredLocales()
	{
		return \Katu\Types\TLocale::getPreferredFromRequest(\Katu\App::get()->request->headers->get('Accept-Language'));
	}

	public static function getPreferredLocale($locale = null)
	{
		if ($locale) {
			return $locale;
		}

		$preferredLocaleCollection = static::getPreferredLocales();
		if (isset($preferredLocaleCollection[0])) {
			return $preferredLocaleCollection[0];
		}

		return false;
	}

	public static function getLocalNumber($locale, $number)
	{
		try {
			$numberFormatter = new \NumberFormatter(static::getPreferredLocale($locale), \NumberFormatter::DECIMAL);

			return $numberFormatter->format($number);
		} catch (\Throwable $e) {
			return $number;
		}
	}

	public static function getLocalFormNumber($locale, $number, $decimals = 2)
	{
		try {
			$numberFormatter = new \NumberFormatter(static::getPreferredLocale($locale), \NumberFormatter::DECIMAL);
			$numberFormatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $decimals);

			$res = $numberFormatter->format($number);
			$res = preg_replace('/\s/u', null, $res);

			return $res;
		} catch (\Throwable $e) {
			return $number;
		}
	}

	public static function getLocalReadableNumber($locale, $number, $digits = null)
	{
		try {
			$numberFormatter = new \NumberFormatter(static::getPreferredLocale($locale), \NumberFormatter::DECIMAL);
			$numberFormatter->setAttribute(\NumberFormatter::DECIMAL_ALWAYS_SHOWN, false);

			if ($digits !== null) {
				$numberFormatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $digits);
			} else {
				$numberFormatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 3);
				if ($number >= 1) {
					$numberFormatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 2);
				}
				if ($number >= 10) {
					$numberFormatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 1);
				}
				if ($number >= 100) {
					$numberFormatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 0);
				}
			}

			return $numberFormatter->format($number);
		} catch (\Throwable $e) {
			return $number;
		}
	}

	public static function getLocalPercent($locale, $number, $decimalPlaces = 0)
	{
		$numberFormatter = new \NumberFormatter(static::getPreferredLocale($locale), \NumberFormatter::PERCENT);
		$numberFormatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $decimalPlaces);

		return $numberFormatter->format($number);
	}

	public static function getLocalCurrency($locale, $number, $currency)
	{
		$numberFormatter = new \NumberFormatter(static::getPreferredLocale($locale), \NumberFormatter::CURRENCY);

		return $numberFormatter->formatCurrency($number, $currency);
	}

	public static function getLocalWholeCurrency($locale, $number, $currency)
	{
		$numberFormatter = new \NumberFormatter(static::getPreferredLocale($locale), \NumberFormatter::CURRENCY);
		$numberFormatter->setTextAttribute(\NumberFormatter::CURRENCY_CODE, $currency);
		$numberFormatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 0);

		return $numberFormatter->format($number);
	}
}
