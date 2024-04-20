<?php

namespace Katu\Tools\Intl;

class Formatter
{
	protected $locale;

	public function __construct(Locale $locale)
	{
		$this->setLocale($locale);
	}

	public function setLocale(Locale $locale): Formatter
	{
		$this->locale = $locale;

		return $this;
	}

	public function getLocale(): Locale
	{
		return $this->locale;
	}

	public function getLocalNumber($number)
	{
		$numberFormatter = new \NumberFormatter($this->getLocale(), \NumberFormatter::DECIMAL);

		return $numberFormatter->format($number);
	}

	public function getLocalFormNumber($number)
	{
		return preg_replace("/\s/u", "", $this->getLocalNumber($number));
	}

	public function getLocalReadableNumber($number)
	{
		$numberFormatter = new \NumberFormatter($this->getLocale(), \NumberFormatter::DECIMAL);
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

	public function getLocalDecimalNumber($number, int $digits = 0)
	{
		$numberFormatter = new \NumberFormatter($this->getLocale(), \NumberFormatter::DECIMAL);
		$numberFormatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, $digits);
		$numberFormatter->setAttribute(\NumberFormatter::DECIMAL_ALWAYS_SHOWN, (bool)$digits);

		return $numberFormatter->format($number);
	}

	public function getLocalPercent(float $number, int $decimals = 0)
	{
		$numberFormatter = new \NumberFormatter($this->getLocale(), \NumberFormatter::PERCENT);
		$numberFormatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $decimals);
		$numberFormatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $decimals);

		return $numberFormatter->format($number);
	}

	public function getLocalCurrency(float $number, string $currencyCode)
	{
		$number = (string)$number;
		$numberFormatter = new \NumberFormatter($this->getLocale(), \NumberFormatter::CURRENCY);
		if ((int)$number == (float)$number) {
			$numberFormatter->setTextAttribute(\NumberFormatter::CURRENCY_CODE, $currencyCode);
			$numberFormatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 0);
			$numberFormatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 2);
		}

		return $numberFormatter->formatCurrency((float)$number, $currencyCode);
	}

	public function getLocalWholeCurrency(float $number, string $currencyCode)
	{
		$number = (string)$number;
		$numberFormatter = new \NumberFormatter($this->getLocale(), \NumberFormatter::CURRENCY);
		$numberFormatter->setTextAttribute(\NumberFormatter::CURRENCY_CODE, $currencyCode);
		$numberFormatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 0);

		return $numberFormatter->format((float)$number);
	}
}
