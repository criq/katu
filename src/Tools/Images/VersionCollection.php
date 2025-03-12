<?php

namespace Katu\Tools\Images;

class VersionCollection extends \ArrayObject
{
	public function filterByTitle(string $title): VersionCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Version $version) use ($title) {
			return $version->getTitle() == $title;
		})));
	}

	public function getFirst(): ?Version
	{
		return array_values($this->getArrayCopy())[0] ?? null;
	}

	public function getAssoc(): VersionCollection
	{
		return new static(array_combine(
			array_map(function (Version $version) {
				return $version->getTitle();
			}, $this->getArrayCopy()),
			$this->getArrayCopy(),
		));
	}
}
