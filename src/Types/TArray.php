<?php

namespace Katu\Types;

class TArray implements \ArrayAccess, \IteratorAggregate {

	public $array;

	public function __construct($value = []) {
		if (!self::isValid($value)) {
			throw new \Exception("Invalid array.");
		}

		$this->array = $value;
	}

	public function getArray() {
		return $this->array;
	}

	static function isValid($value) {
		return is_array($value);
	}

	public function getValueByArgs() {
		$value = $this->array;

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

		foreach ($this->array as $key => $value) {
			if (in_array($key, func_get_args()) === false) {
				$res[$key] = $value;
			}
		}

		return new self($res);
	}

	public function implodeInSentence($delimiter, $lastDelimiter) {
		$arrayList = (array) array_slice($this->array, 0, -1);
		$arrayLast = (array) array_slice($this->array, -1, 1);

		return implode($lastDelimiter, array_filter([implode($delimiter, $arrayList), $arrayLast[0]]));
	}

	public function implodeWithKeys($itemDelimiter, $keyValueDelimiter = null) {
		if (!$keyValueDelimiter) {
			$keyValueDelimiter = $itemDelimiter;
		}

		$items = [];
		foreach ($this->array as $key => $value) {
			$items[] = implode($keyValueDelimiter, [$key, $value]);
		}

		return implode($itemDelimiter, $items);
	}

	public function getRandomItems($n) {
		$array = [];
		for ($i = 0; $i < $n; $i++) {
			$res[] = $this->array[array_rand($this->array)];
		}
		return $res;
	}

	public function flatten() {
		$iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($this));
		$values = [];

		foreach ($iterator as $value) {
			$values[] = $value;
		}

		return $values;
	}

	public function unique() {
		return new static(array_unique($this->array));
	}

	public function values() {
		return new static(array_values($this->array));
	}

	public function natsort() {
		$array = $this->array;
		natsort($array);

		return new static($array);
	}

	/* ArrayAccess ***********************************************************/

	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->array[] = $value;
		} else {
			$this->array[$offset] = $value;
		}
	}

	public function offsetExists($offset) {
		return isset($this->array[$offset]);
	}

	public function offsetUnset($offset) {
		unset($this->array[$offset]);
	}

	public function offsetGet($offset) {
		return isset($this->array[$offset]) ? $this->array[$offset] : null;
	}

	/* IteratorAggregate *****************************************************/

	public function getIterator() {
		return new \ArrayIterator($this->array);
	}

}
