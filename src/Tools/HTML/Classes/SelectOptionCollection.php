<?php

namespace Katu\Tools\HTML\Classes;

use Katu\Tools\HTML\NodeCollection;

class SelectOptionCollection extends \ArrayObject
{
	public function getNodes(?string $selectedValue = null): NodeCollection
	{
		return new NodeCollection(array_map(function (SelectOption $selectOption) use ($selectedValue) {
			return $selectOption->getNode($selectedValue);
		}, $this->getArrayCopy()));
	}
}
