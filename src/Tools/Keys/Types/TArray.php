<?php

namespace Katu\Tools\Keys\Types;

class TArray extends \Katu\Tools\Keys\Key {

	public function createType($source) {
		// Array.
		if (is_array($source)) {
			$key = new static($source);

		// URL.
		} elseif ($source instanceof \Katu\Types\TURL) {
			$key = new \Katu\Tools\Keys\Types\TURL($source);

		// String.
		} elseif (is_string($source)) {
			$key = new \Katu\Tools\Keys\Types\TString($source);

		// Number.
		} elseif (is_numeric($source)) {
			$key = new \Katu\Tools\Keys\Types\TNumber($source);

		// Generic.
		} else {
			$key = new \Katu\Tools\Keys\Types\Generic($source);

		}

		$key
			->setDelimiter($this->delimiter)
			->setHashPrefixLength($this->hashPrefixLength)
			->setHashPrefixCount($this->hashPrefixCount)
			;

		return $key;
	}

	public function getParts() {
		$parts = new \Katu\Types\TArray;
		$source = is_array($this->source) ? $this->source : [$this->source];

		foreach ($source as $item) {
			$parts->append($this->createType($item)->getParts());
		}

		return $parts->filter()->values()->getArray();
	}

}
