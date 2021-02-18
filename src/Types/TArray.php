<?php

namespace Katu\Types;

class TArray extends \ArrayObject
{
	public function getArray()
	{
		return $this->getArrayCopy();
	}

	public function getTotal()
	{
		return $this->count();
	}

	public function getCount()
	{
		return $this->count();
	}

	public function getValueByArgs()
	{
		$value = $this->getArray();

		foreach (func_get_args() as $key) {
			if (isset($value[$key])) {
				$value = $value[$key];
			} else {
				throw new \Katu\Exceptions\MissingArrayKeyException("Invalid key " . $key . ".");
			}
		}

		return $value;
	}

	public function getIndex($index)
	{
		return $this[$index];
	}

	public function getWithoutKeys()
	{
		$res = [];
		foreach ($this as $key => $value) {
			if (in_array($key, func_get_args()) === false) {
				$res[$key] = $value;
			}
		}

		return new static($res);
	}

	public function implodeInSentence($delimiter, $lastDelimiter)
	{
		$arrayList = (array)array_slice($this->getArray(), 0, -1);
		$arrayLast = (array)array_slice($this->getArray(), -1, 1);

		return implode($lastDelimiter, array_filter([implode($delimiter, $arrayList), $arrayLast[0]]));
	}

	public function implodeWithKeys($itemDelimiter, $keyValueDelimiter = null)
	{
		if (!$keyValueDelimiter) {
			$keyValueDelimiter = $itemDelimiter;
		}

		$items = [];
		foreach ($this as $key => $value) {
			$items[] = implode($keyValueDelimiter, [$key, $value]);
		}

		return implode($itemDelimiter, $items);
	}

	public function mapToValue($value, $default = null)
	{
		if (isset($this[$value])) {
			return $this[$value];
		}

		return $default;
	}

	public function getRandomItems($n)
	{
		for ($i = 0; $i < $n; $i++) {
			$res[] = $this[array_rand($this->getArray())];
		}
		return $res;
	}

	public function flatten()
	{
		$iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($this));
		$values = [];

		foreach ($iterator as $value) {
			$values[] = $value;
		}

		return new static($values);
	}

	public function reverse()
	{
		return new static(array_reverse($this->getArray()));
	}

	public function unique()
	{
		return new static(array_unique($this->getArray()));
	}

	public function keys()
	{
		return new static(array_keys($this->getArray()));
	}

	public function values()
	{
		return new static(array_values($this->getArray()));
	}

	public function natsort()
	{
		$array = $this->getArray();
		natsort($array);

		return new static($array);
	}

	public function asort($sortFlags = \SORT_REGULAR)
	{
		$array = $this->getArray();
		asort($array, $sortFlags);

		return new static($array);
	}

	public function ksort($sortFlags = \SORT_REGULAR)
	{
		$array = $this->getArray();
		ksort($array, $sortFlags);

		return new static($array);
	}

	public function shuffle()
	{
		$array = $this->getArray();
		shuffle($array);

		return new static($array);
	}

	public function slice($offset, $length, $preserveKeys = false)
	{
		return new static(array_slice($this->getArray(), $offset, $length, $preserveKeys));
	}

	public function getPage($page, $perPage)
	{
		return new static(array_slice($this->getArray(), (($page - 1) * $perPage), $perPage));
	}

	public function orderBy($key, $flags = 0)
	{
		$array = $this->getArray();

		array_multisort(array_map(function ($i) use ($key) {
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

	public function map($callback)
	{
		$array = array_map($callback, $this->getArray());

		return new static($array);
	}

	public function filter($callback = null)
	{
		if (!$callback) {
			$callback = function ($i) {
				return (bool)$i;
			};
		}

		$array = array_filter($this->getArray(), $callback);

		return new static($array);
	}

	public function search($needle)
	{
		return array_search($needle, $this->getArray());
	}

	public function contains($needle)
	{
		return $this->search($needle) !== false;
	}
}
