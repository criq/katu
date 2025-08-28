<?php

namespace Katu\Tools\HTTPTools;

class HeaderCollection extends \ArrayObject
{
	public function filterByName(string $name): HeaderCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Header $header) use ($name) {
			return $header->hasName($name);
		})));
	}

	public function filterByValueRegex(string $regex): HeaderCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Header $header) use ($regex) {
			return preg_match($regex, $this->getValue());
		})));
	}

	public function filterNotNull(): HeaderCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Header $header) {
			return !is_null($header->getValue());
		})));
	}

	public function getFirst(): ?Header
	{
		return array_values($this->getArrayCopy())[0] ?? null;
	}

	public function getValue(): ?string
	{
		return $this->getFirst() ? $this->getFirst()->getValue() : null;
	}

	public function setHeader(Header $header): HeaderCollection
	{
		$this[] = $header;

		return $this;
	}
}
