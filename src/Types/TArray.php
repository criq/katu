<?php

namespace Katu\Types;

class TArray implements \ArrayAccess, \IteratorAggregate, \Countable {

	private $array = [];

	public function __construct($value = []) {
		if (!self::isValid($value)) {
			throw new \Exception("Invalid array.");
		}

		if ($value instanceof static) {
			$this->array = $value->getArray();
		} else {
			$this->array = (array)$value;
		}
	}

	public function getArray() {
		return $this->array;
	}

	static function isValid($value) {
		return is_array($value) || $value instanceof static;
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

	public function getIndex($index) {
		return $this[$index];
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

	public function mapToValue($value, $default = null) {
		if (isset($this[$value])) {
			return $this[$value];
		}

		return $default;
	}

	public function getRandomItems($n) {
		$array = [];
		for ($i = 0; $i < $n; $i++) {
			$res[] = $this->array[array_rand($this->array)];
		}
		return $res;
	}

	public function flatten() {
		$iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($this->array));
		$values = [];

		foreach ($iterator as $value) {
			$values[] = $value;
		}

		return $values;
	}

	public function reverse() {
		return new static(array_reverse($this->array));
	}

	public function unique() {
		return new static(array_unique($this->array));
	}

	public function keys() {
		return new static(array_keys($this->array));
	}

	public function values() {
		return new static(array_values($this->array));
	}

	public function natsort() {
		$array = $this->array;
		natsort($array);

		return new static($array);
	}

	public function asort($sortFlags = \SORT_REGULAR) {
		$array = $this->array;
		asort($array, $sortFlags);

		return new static($array);
	}

	public function ksort($sortFlags = \SORT_REGULAR) {
		$array = $this->array;
		ksort($array, $sortFlags);

		return new static($array);
	}

	public function shuffle() {
		$array = $this->array;
		shuffle($array);

		return new static($array);
	}

	public function slice($offset, $length, $preserveKeys = false) {
		return new static(array_slice($this->array, $offset, $length, $preserveKeys));
	}

	public function append($array) {
		$this->array = array_merge($this->array, (new static($array))->getArray());

		return true;
	}

	public function getPage($page, $perPage) {
		return new static(array_slice($this->array, (($page - 1) * $perPage), $perPage));
	}

	public function orderBy($key, $flags = 0) {
		$array = $this->array;

		array_multisort(array_map(function($i) use($key) {
			if (is_object($i) && method_exists($i, $key)) {
				return call_user_func_array([$i, $key], []);
			} elseif (is_object($i) && isset($i->$key)) {
				return $i->$key;
			} elseif (is_object($i) && is_callable([$i, $key])) {
				return call_user_func_array([$i, $key], []);
			} elseif (is_array($i)) {
				return $i[$key];
			}
		}, $array), $array, $flags);

		return new static($array);
	}

	public function count() {
		return count($this->array);
	}

	public function map($callback) {
		$array = array_map($callback, $this->array);

		return new static($array);
	}

	public function pushArray($array) {
		foreach ($array as $item) {
			array_push($this->array, $item);
		}

		return true;
	}

	public function getTotal() {
		return $this->count();
	}

	public function getCount() {
		return $this->count();
	}

	/* ArrayAccess **************************************************************/

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

	/* IteratorAggregate ********************************************************/

	public function getIterator() {
		return new \ArrayIterator($this->array);
	}

}
