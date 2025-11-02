<?php

namespace Katu\Tools\Images;

use Katu\Tools\Strings\Code;
use Katu\Tools\Strings\CodeCollection;

class VersionCollection extends \ArrayObject
{
	public static function createFromCodes(CodeCollection $codes): VersionCollection
	{
		return new static(array_values(array_filter(array_map(function (Code $code) {
			return Version::createFromCode($code);
		}, $codes->getArrayCopy()))));
	}

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
