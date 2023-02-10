<?php

namespace Katu\Tools\Options;

class OptionCollection extends \ArrayObject
{
	public function __construct(?array $array = [])
	{
		foreach ($array as $item) {
			$this[$item->getName()] = $item;
		}
	}

	public function mergeWith(?OptionCollection $optionCollection = null)
	{
		$res = clone $this;
		if ($optionCollection) {
			foreach ($optionCollection as $option) {
				$res->append($option);
			}
		}

		return $res;
	}

	public function filterByName(string $name): OptionCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function ($option) use ($name) {
			return $option->getName() == $name;
		})));
	}

	public function getByName(string $name): ?Option
	{
		try {
			return array_values($this->filterByName($name)->getArrayCopy())[0] ?? null;
		} catch (\Throwable $e) {
			return null;
		}
	}

	public function getValue(string $name)
	{
		try {
			return $this->getByName($name)->getValue();
		} catch (\Throwable $e) {
			return null;
		}
	}
}
