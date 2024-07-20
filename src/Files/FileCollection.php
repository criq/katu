<?php

namespace Katu\Files;

class FileCollection extends \ArrayObject
{
	public function filterByExtension(string $extension): FileCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (File $file) use ($extension) {
			return mb_strtolower($file->getExtension()) == mb_strtolower($extension);
		})));
	}
}
