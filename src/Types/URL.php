<?php

namespace Jabli\Types;

class URL {

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
		$parsed = parse_url($this->value);

		if (!$overwrite && isset($parsed['query'][$name])) {
			throw new Exception("Query param already exists.");
		}

		$parsed['query'][$name] = $value;

		return self::buildURL($parsed);
	}

	static function buildURL($params) {


		var_dump($parsed);

		/*
		scheme - e.g. http
		host
		port
		user
		pass
		path
		query - after the question mark ?
		fragment - after the hashmark #
		*/
	}

}
