<?php

namespace Jabli\Types;

class URL {

	const DEFAULT_SCHEME = 'http';

	public $value;

	public function __construct($value) {
		if (!self::isValid($value)) {
			throw new Exception("Invalid URL.");
		}

		$this->value = (string) (trim($value));
	}

	static function isValid($value) {
		return filter_var(trim($value), FILTER_VALIDATE_URL) !== FALSE;
	}

	public function addParam($name, $value, $overwrite = TRUE) {
		$parts = parse_url($this->value);

		if (!$overwrite && isset($parts['query'][$name])) {
			throw new Exception("Query param already exists.");
		}

		$parts['query'][$name] = $value;

		$this->value = self::buildURL($parts);

		return TRUE;
	}

	static function buildURL($parts) {
		$url = '';

		if (!isset($parts['host'])) {
			throw new Exception("Missing host");
		}

		if (!isset($parts['scheme'])) {
			$url .= self::DEFAULT_SCHEME;
		} else {
			$url .= $parts['scheme'];
		}

		$url .= '://' . $parts['host'];

		if (isset($parts['path'])) {
			$url .= $parts['path'];
		}

		if (isset($parts['query'])) {
			$url .= '?' . http_build_query($parts['query']);
		}

		return $url;
	}

}
