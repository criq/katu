<?php

namespace Katu\Tools\HTML;

class AttributeCollection extends \ArrayObject implements HTMLInterface
{
	public function __toString(): string
	{
		return (string)$this->getHTML();
	}

	public static function createFromArray(array $array) {
		return new static(array_map(function (string $value, string $key) {
			return new Attribute($key, $value);
		}, $array, array_keys($array)));
	}

	public function getHTML(): HTML
	{
		return new HTML(implode(" ", array_map(function (Attribute $attribute) {
			return $attribute->getHTML();
		}, $this->sort()->getArrayCopy())));
	}

	public function offsetSet($key, $value)
	{
		parent::offsetSet($value->getName(), $value);
	}

	public function addAttribute(Attribute $attribute): AttributeCollection
	{
		$this->append($attribute);

		return $this;
	}

	public function getAttribute(string $name): ?Attribute
	{
		return $this[$name] ?? null;
	}

	public function sort(): AttributeCollection
	{
		$array = $this->getArrayCopy();
		usort($array, function (Attribute $a, Attribute $b) {
			return $a->getName() < $b->getName() ? -1 : 1;
		});

		return new static($array);
	}
}
