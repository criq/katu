<?php

namespace Katu\Cache\Path;

class Raw extends Segment {

	protected $value;

	public function __construct($value) {
		$this->value = $value;
	}

	public function getSanitized() {
		$value = $this->value;

		// Numbers and strings.
		if (is_string($value) || is_int($value) || is_float($value)) {
			$value = (string)$value;

		// Other types.
		} else {
			$value = static::generateHashPathSegment(serialize($value));
		}

		// Sanitize.
		$value = (new \Katu\Types\TString($value))->normalizeSpaces()->trim();
		$value = preg_replace('/\v/m', ' ', $value);
		$value = strtr($value, [
			'{closure}' => 'closure',
		]);
		$values = preg_split('/[\/\\\]/', $value);
		$values = array_map(function($i) {
			return (new \Katu\Types\TString($i))->getForUrl([
				'lowercase' => false,
			]);
		}, $values);
		$value = implode('/', $values);
		$value = preg_replace('/[^0-9a-z\-\_\.\/]/i', '-', $value);
		if (mb_strlen($value) > 128) {
			$value = implode('/', static::generateHashArray($value));
		}

		return new Sanitized($value);
	}

	static function generateHashArray($value) {
		$hash = sha1($value);

		$array = [
			'_' . substr($hash, 0, 2),
			'_' . substr($hash, 2, 2),
			'_' . substr($hash, 4),
		];

		return $array;
	}

	static function generateHashPathSegment($value) {
		return implode('/', static::generateHashArray($value));
	}

}
