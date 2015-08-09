<?php

namespace Katu\Types;

class TString {

	public $value;

	public function __construct($value) {
		if (!self::isValid($value)) {
			throw new \Exception("Invalid string.");
		}

		$this->value = $value;
	}

	public function __toString() {
		return $this->value;
	}

	static function isValid($value) {
		return is_string($value);
	}

	public function getNumberOfWords() {
		return count(array_filter(explode(' ', $this->value)));
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

		return \URLify::filter($this->value, isset($options['maxLength']) ? $options['maxLength'] : 255, isset($options['language']) ? $options['language'] : null);
	}

	public function getAsFloat() {
		return (float) floatval(trim(strtr(preg_replace('#[\s]#u', null, $this->value), ',', '.')));
	}

	public function getAsArray() {
		$chars = [];
		for ($i = 0; $i < mb_strlen($this->value); $i++) {
			$chars[] = mb_substr($this->value, $i, 1);
		}

		return $chars;
	}

	public function getWbr() {
		return implode('<wbr />', $this->getAsArray());
	}

}
