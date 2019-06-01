<?php

namespace Katu\Tools\Strings;

class Key {

	private $source;

	private $delimiter        = "/";
	private $hashPrefixLength = 2;
	private $hashPrefixCount  = 3;

	public function __construct($source) {
		$this->source = $source;
	}

	public function __toString() {
		return $this->getKey();
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

	public function getKey() {
		$parts = new \Katu\Types\TArray;

		$args = is_array($this->source) ? $this->source : [$this->source];
		$args = array_values(array_filter($args));

		foreach ($args as $arg) {

			// String or URL.
			if (is_string($arg) || $arg instanceof \Katu\Types\TURL) {

				// Try URL.
				try {

					$url = new \Katu\Types\TURL($arg);
					$urlParts = $url->getParts();
					$parts->append($url->getScheme());
					$parts->append(explode('.', $url->getHost()));
					if (isset($urlParts['path'])) {
						$parts->append(explode('/', $urlParts['path']));
					}
					if (isset($urlParts['query'])) {
						$parts->append($this->getHashWithPrefix($urlParts['query']));
					}

				// String.
				} catch (\Exception $e) {

					$parts->append($this->getSanitizedString($arg));

				}

			// Number.
			} elseif (is_numeric($arg)) {
				$parts->append($this->getSanitizedString($arg));

			// Array.
			} elseif (is_array($arg)) {
				$parts->append((new static($arg))->getKey());

			// Another datatype.
			} else {
				$parts->append($this->getHashWithPrefix($arg));

			}

		}

		return implode($this->delimiter, $parts->filter()->values()->getArray());
	}

}
