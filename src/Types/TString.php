<?php

namespace Katu\Types;

class TString {

	public $string;

	public function __construct($string) {
		if (!self::isValid($string)) {
			throw new \Exception("Invalid string.");
		}

		$this->string = $string;
	}

	public function __toString() {
		return $this->string;
	}

	static function isValid($string) {
		return is_string($string) || is_int($string) || is_float($string);
	}

	public function getNumberOfWords() {
		return count(array_filter(explode(' ', $this->string)));
	}

	public function hasAtLeastWords($n) {
		return $this->getNumberOfWords() >= $n;
	}

	public function getForUrl($options = []) {
		$options = $options + [
			'delimiter' => '-',
			'lowercase' => true,
		];

		\URLify::$remove_list = [];

		return \URLify::filter($this->string, isset($options['maxLength']) ? $options['maxLength'] : 255, isset($options['language']) ? $options['language'] : null);
	}

	public function getAsFloat() {
		return (float) floatval(trim(strtr(preg_replace('/\s/u', null, $this->string), ',', '.')));
	}

	public function getAsArray() {
		$chars = [];
		for ($i = 0; $i < mb_strlen($this->string); $i++) {
			$chars[] = mb_substr($this->string, $i, 1);
		}

		return $chars;
	}

	public function getWbr() {
		return implode('<wbr />', $this->getAsArray());
	}

}
