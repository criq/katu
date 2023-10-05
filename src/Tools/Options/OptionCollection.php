<?php

namespace Katu\Tools\Options;

use Katu\Tools\Strings\Code;

class OptionCollection extends \ArrayObject
{
	public function __construct()
	{
		foreach (func_get_args()[0] ?? [] as $option) {
			$this[] = $option;
		}
	}

	public static function createFromArray(array $array): OptionCollection
	{
		$res = new static;
		foreach ($array as $key => $value) {
			$res[] = new Option((new Code($key))->getConstantFormat(), $value);
		}

		return $res;
	}

	public function offsetSet($key, $option): void
	{
		if ($option instanceof Option && !is_null($option->getValue())) {
			parent::offsetSet((string)$option->getCode(), $option);
		}
	}

	public function getMergedWith(?OptionCollection $options = null): OptionCollection
	{
		$res = new static;

		foreach ($this as $option) {
			$res[] = $option;
		}
		foreach (($options ?: new OptionCollection) as $option) {
			$res[] = $option;
		}

		return $res;
	}

	public function filterByCode(Code $code): OptionCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Option $option) use ($code) {
			return $option->getCode()->getConstantFormat() == $code->getConstantFormat();
		})));
	}

	public function sort(): OptionCollection
	{
		$array = $this->getArrayCopy();
		usort($array, function (Option $a, Option $b) {
			return $a->getCode()->getConstantFormat() < $b->getCode()->getConstantFormat() ? -1 : 1;
		});

		return new static($array);
	}

	public function getByCode(Code $code): ?Option
	{
		try {
			return array_values($this->filterByCode($code)->getArrayCopy())[0] ?? null;
		} catch (\Throwable $e) {
			return null;
		}
	}

	public function getValue(string $code)
	{
		try {
			return $this->getByCode(new Code($code))->getValue();
		} catch (\Throwable $e) {
			return null;
		}
	}
}
