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

	static function isValid($value) {
		return is_string($value);
	}

	public function getNumberOfWords() {
		return count(array_filter(explode(' ', $this->value)));
	}

	public function hasAtLeastWords($n) {
		return $this->getNumberOfWords() >= $n;
	}

	public function getAsSlug($options = array()) {
		$options = array_merge(array(
			'delimiter' => '-',
			'lowercase' => TRUE,
		), $options);

		$translationTable = \Katu\Utils\YAML::decode(file_get_contents(realpath(dirname(__FILE__) . '/../Config/asciiTranslationTable.yaml')));

		var_dump($translationTable); die;

		return strtr($this->value, $translationTable);
	}

}
