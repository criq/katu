<?php

namespace Katu\Tools\HTML\Classes;

use Katu\Tools\HTML\NodeCollection;

class SelectOptionCollection extends \ArrayObject
{
	public function getNodes(): NodeCollection
	{
		return new NodeCollection(array_map(function (SelectOption $selectOption) {
			return $selectOption->getNode();
		}, $this->getArrayCopy()));
	}
}
