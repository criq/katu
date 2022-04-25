<?php

namespace Katu\PDO;

class ColumnDescriptionCollection extends \ArrayObject
{
	public function filterByName(string $name): ColumnDescriptionCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (ColumnDescription $columnDescription) use ($name) {
			return $columnDescription->getName() == $name;
		})));
	}
}
