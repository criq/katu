<?php

namespace Katu\Tools\Options;

use Katu\Tools\Strings\Code;

class OptionCollection extends \ArrayObject
{
	public function mergeWith(?OptionCollection $options = null): OptionCollection
	{
		$res = clone $this;
		if ($options) {
			foreach ($options as $option) {
				$res->append($option);
			}
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

	public function getValue(Code $code)
	{
		try {
			return $this->getByCode($code)->getValue();
		} catch (\Throwable $e) {
			return null;
		}
	}
}
