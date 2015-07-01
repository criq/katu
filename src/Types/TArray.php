<?php

namespace Katu\Types;

class TArray implements \ArrayAccess, \IteratorAggregate {

	public $position = 0;
	public $container;

	public function __construct($value = []) {
		if (!self::isValid($value)) {
			throw new \Exception("Invalid array.");
		}

		$this->container = $value;
	}

	public function getArray() {
		return $this->container;
	}

	/*************************************************************************
	 * ArrayAccess.
	 */

	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->container[] = $value;
		} else {
			$this->container[$offset] = $value;
		}
	}

	public function offsetExists($offset) {
		return isset($this->container[$offset]);
	}

	public function offsetUnset($offset) {
		unset($this->container[$offset]);
	}

	public function offsetGet($offset) {
		return isset($this->container[$offset]) ? $this->container[$offset] : null;
	}

	/*************************************************************************
	 * IteratorAggregate.
	 */

	public function getIterator() {
		return new \ArrayIterator($this->container);
	}

	/*************************************************************************
	 * TArray.
	 */

	static function isValid($value) {
		return is_array($value);
	}

	public function getValueByArgs() {
		$value = $this->container;

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

		foreach ($this->container as $key => $value) {
			if (in_array($key, func_get_args()) === false) {
				$res[$key] = $value;
			}
		}

		return new self($res);
	}

	public function implodeInSentence($delimiter, $lastDelimiter) {
		$arrayList = (array) array_slice($this->container, 0, -1);
		$arrayLast = (array) array_slice($this->container, -1, 1);

		return implode($lastDelimiter, array_filter([implode($delimiter, $arrayList), $arrayLast[0]]));
	}

	public function implodeWithKeys($itemDelimiter, $keyValueDelimiter = null) {
		if (!$keyValueDelimiter) {
			$keyValueDelimiter = $itemDelimiter;
		}

		$items = [];
		foreach ($this->container as $key => $value) {
			$items[] = implode($keyValueDelimiter, [$key, $value]);
		}

		return implode($itemDelimiter, $items);
	}

	public function getRandomItems($n) {
		$array = [];
		for ($i = 0; $i < $n; $i++) {
			$res[] = $this->container[array_rand($this->container)];
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

}
