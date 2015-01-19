<?php

namespace Katu\Types;

class TArray {

	public $value;

	public function __construct($value) {
		if (!self::isValid($value)) {
			throw new \Exception("Invalid e-mail address.");
		}

		$this->value = $value;
	}

	static function isValid($value) {
		return is_array($value);
	}

	public function getValueByArgs() {
		$value = $this->value;

		foreach (func_get_args() as $key) {
			if (isset($value[$key])) {
				$value = $value[$key];
			} else {
				throw new \Katu\Exceptions\MissingArrayKeyException("Invalid key " . $key . ".");
			}
		}

		return $value;
	}

	public function getWithoutKeys() {
		$res = array();

		foreach ($this->value as $key => $value) {
			if (!in_array($key, func_get_args())) {
				$res[$key] = $value;
			}
		}

		return new self($res);
	}

	public function implodeInSentence($delimiter, $lastDelimiter) {
		$arrayList = (array) array_slice($this->value, 0, -1);
		$arrayLast = (array) array_slice($this->value, -1, 1);

		return implode($lastDelimiter, array_filter([implode($delimiter, $arrayList), $arrayLast[0]]));
	}

	public function implodeWithKeys($itemDelimiter, $keyValueDelimiter = null) {
		if (!$keyValueDelimiter) {
			$keyValueDelimiter = $itemDelimiter;
		}

		$items = [];
		foreach ($this->value as $key => $value) {
			$items[] = implode($keyValueDelimiter, [$key, $value]);
		}

		return implode($itemDelimiter, $items);
	}

	public function getRandomItems($n) {
		$array = [];
		for ($i = 0; $i < $n; $i++) {
			$res[] = $this->value[array_rand($this->value)];
		}
		return $res;
	}

}
