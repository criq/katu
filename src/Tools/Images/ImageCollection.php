<?php

namespace Katu\Tools\Images;

class ImageCollection extends \ArrayObject
{
	public function getFirst(): ?Image
	{
		$items = $this->getArrayCopy();

		return reset($items) ?: null;
	}

	public function filterUsable(): ImageCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Image $image) {
			return $image->getIsUsable();
		})));
	}
}
