<?php

namespace Katu\Tools\Strings;

class SortableCollection extends \ArrayObject
{
	public function __toString(): string
	{
		return implode(".", array_map(function (Sortable $sortable) {
			return $sortable->getSortable();
		}, $this->getArrayCopy()));
	}
}
