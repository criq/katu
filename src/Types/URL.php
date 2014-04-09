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

	public function __toString() {
		return $this->value;
	}

	static function isValid($value) {
		return filter_var(trim($value), FILTER_VALIDATE_URL) !== FALSE;
	}

	public function get2ndLevelDomain() {
		$parsed = parse_url($this->value);
		if (!isset($parsed['host'])) {
			throw new \Jabli\Exception("Invalid URL host.");
		}

		return implode('.', array_slice(explode('.', $parsed['host']), -2));
	}

	public function getParts() {
		$parts = parse_url($this->value);

		if (isset($parts['query'])) {
			parse_str($parts['query'], $query_params);
			$parts['query'] = $query_params;
		}

		return $parts;
	}

	public function addQueryParam($name, $value, $overwrite = TRUE) {
		$parts = $this->getParts();

		if (!$overwrite && isset($parts['query'][$name])) {
			throw new Exception("Query param already exists.");
		}

		$parts['query'][$name] = $value;

		$this->value = self::build($parts);

		return $this;
	}

	public function removeQueryParam($name) {
		$parts = $this->getParts();

		unset($parts['query'][$name]);

		$this->value = self::build($parts);

		return $this;
	}

	static function build($parts) {
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

		if (isset($parts['query']) && $parts['query']) {
			$url .= '?' . http_build_query($parts['query']);
		}

		return $url;
	}

}
