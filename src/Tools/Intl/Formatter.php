<?php

namespace Katu\Tools\Intl;

class Formatter
{

	public static function getPreferredLocales()
	{
		$app = \Katu\App::get();
		$headers = $app->getContainer()->get('request')->getHeader('Accept-Language');

		return ($headers[0] ?? null) ? \Katu\Types\TLocale::getPreferredFromRequest($headers[0]) : [];
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
		$numberFormatter = new \NumberFormatter(static::getPreferredLocale($locale), \NumberFormatter::DECIMAL);

		return $numberFormatter->format($number);
	}

	public static function getLocalReadableNumber($locale, $number)
	{
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

	public static function getLocalPercent($locale, $number)
	{
		$numberFormatter = new \NumberFormatter(static::getPreferredLocale($locale), \NumberFormatter::PERCENT);

		return $numberFormatter->format($number);
	}

	public static function getLocalCurrency($locale, $number, $currency)
	{
		$number = (string)$number;
		$numberFormatter = new \NumberFormatter(static::getPreferredLocale($locale), \NumberFormatter::CURRENCY);
		if ((int)$number == (float)$number) {
			$numberFormatter->setTextAttribute(\NumberFormatter::CURRENCY_CODE, $currency);
			$numberFormatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 0);
		}

		return $numberFormatter->formatCurrency($number, $currency);
	}

	public static function getLocalWholeCurrency($locale, $number, $currency)
	{
		$number = (string)$number;
		$numberFormatter = new \NumberFormatter(static::getPreferredLocale($locale), \NumberFormatter::CURRENCY);
		$numberFormatter->setTextAttribute(\NumberFormatter::CURRENCY_CODE, $currency);
		$numberFormatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 0);

		return $numberFormatter->format($number);
	}
}
