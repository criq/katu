<?php

namespace Katu\Tools\Images;

class ImageCollection extends \ArrayObject
{
	public function getFirst(): ?Image
	{
		return array_values($this->getArrayCopy())[0] ?? null;
	}

	public function filterUsable(): ImageCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Image $image) {
			return $image->getIsUsable();
		})));
	}
}
