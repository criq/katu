<?php

namespace Katu\Tools\Images;

class VersionCollection extends \ArrayObject
{
	public function getAssoc(): VersionCollection
	{
		return new static(array_combine(
			array_map(function (Version $version) {
				return $version->getName();
			}, $this->getArrayCopy()),
			$this->getArrayCopy(),
		));
	}
}
