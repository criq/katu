<?php

namespace Katu\Tools\Options;

use Katu\Tools\Strings\Code;

class OptionCollection extends \ArrayObject
{
	/**
	 * @param Option $value
	 */
	public function offsetSet($key, $value): void
	{
		parent::offsetSet((string)$value->getCode(), $value);
	}

	public function mergeWith(?OptionCollection $options = null): OptionCollection
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
