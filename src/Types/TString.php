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

	public function getForUrl($options = array()) {
		$options = array_merge(array(
			'delimiter' => '-',
			'lowercase' => TRUE,
		), $options);

		return \URLify::filter($this->value, isset($options['maxLength']) ? $options['maxLength'] : NULL, isset($options['language']) ? $options['language'] : NULL);
	}

	public function getAsFloat() {
		return (float) (trim((strtr($this->value, ',', '.'))));
	}

}
