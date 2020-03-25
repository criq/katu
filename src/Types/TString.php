<?php

namespace Katu\Types;

class TString
{
	public $string;

	public function __construct($string)
	{
		if (!self::isValid($string)) {
			throw new \Exception("Invalid string.");
		}

		$this->string = $string;
	}

	public function __toString()
	{
		return (string)$this->string;
	}

	public static function isValid($string)
	{
		return is_string($string) || is_int($string) || is_float($string);
	}

	public function getNumberOfWords()
	{
		return count(array_filter(explode(' ', $this->string)));
	}

	public function hasAtLeastWords($n)
	{
		return $this->getNumberOfWords() >= $n;
	}

	public function getForURL($options = [])
	{
		$options = array_merge($options, [
			'delimiter' => '-',
			'lowercase' => true,
		]);

		\URLify::$remove_list = [];

		$maxLength = $options['maxLength'] ?? 255;
		$language = $options['language'] ?? 'en';

		return \URLify::filter($this->string, $maxLength, $language);
	}

	public function getAsFloat()
	{
		return (float)floatval(trim(strtr(preg_replace('/\s/u', null, $this->string), ',', '.')));
	}

	public function getAsFloatIfNumeric()
	{
		if (preg_match('/^(([0-9]+(\.[0-9]+)?)|(\.[0-9]+))$/', $this->string, $match)) {
			return (float)$this->string;
		}

		return (string)$this->string;
	}

	public function getAsArray()
	{
		$chars = [];
		for ($i = 0; $i < mb_strlen($this->string); $i++) {
			$chars[] = mb_substr($this->string, $i, 1);
		}

		return $chars;
	}

	public function getWbr()
	{
		return implode('<wbr />', $this->getAsArray());
	}

	public function normalizeSpaces()
	{
		return new static(str_replace("\xc2\xa0", "\x20", $this));
	}

	public function trim()
	{
		return new static(trim($this));
	}
}
