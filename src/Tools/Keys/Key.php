<?php

namespace Katu\Tools\Keys;

class Key {

	protected $source;
	protected $delimiter        = "/";
	protected $hashPrefixLength = 2;
	protected $hashPrefixCount  = 3;

	public function __construct($source) {
		$this->source = $source;
	}

	public function __toString() {
		return $this->getKey();
	}

	public function createType($source) {
		// Array.
		if (is_array($source)) {
			$key = new static($source);

		// URL.
		} elseif ($source instanceof \Katu\Types\TURL) {
			$key = new Types\TURL($source);

		// String.
		} elseif (is_string($source)) {
			$key = new Types\TString($source);

		// Number.
		} elseif (is_numeric($source)) {
			$key = new Types\TNumber($source);

		// Generic.
		} else {
			$key = new Types\Generic($source);

		}

		$key
			->setDelimiter($this->delimiter)
			->setHashPrefixLength($this->hashPrefixLength)
			->setHashPrefixCount($this->hashPrefixCount)
			;

		return $key;
	}

	public function setDelimiter($delimiter) {
		$this->delimiter = $delimiter;

		return $this;
	}

	public function setHashPrefixLength($hashPrefixLength) {
		$this->hashPrefixLength = $hashPrefixLength;

		return $this;
	}

	public function setHashPrefixCount($hashPrefixCount) {
		$this->hashPrefixCount = $hashPrefixCount;

		return $this;
	}

	public function getHash($arg) {
		return sha1(var_export($arg, true));
	}

	public function getHashPrefix($arg) {
		return array_slice(str_split($this->getHash($arg), $this->hashPrefixLength), 0, $this->hashPrefixCount);
	}

	public function getHashWithPrefix($arg) {
		$array = new \Katu\Types\TArray;
		$array->append($this->getHashPrefix($arg));
		$array->append($this->getHash($arg));

		return $array;
	}

	public function getSanitizedString($string) {
		return array_map(function($i) {
			return (new \Katu\Types\TString($i))->getForUrl([
				'delimiter' => $this->delimiter,
				'lowercase' => true,
			]);
		}, preg_split("/[^\d\pL]/ui", $string));
	}

	public function getParts() {
		$parts = new \Katu\Types\TArray;
		$source = is_array($this->source) ? $this->source : [$this->source];

		foreach ($source as $item) {
			$parts->append($this->createType($item)->getParts());
		}

		return $parts->filter()->values()->getArray();
	}

	public function getKey() {
		$key = implode($this->delimiter, $this->getParts());

		return $key;
	}

}
