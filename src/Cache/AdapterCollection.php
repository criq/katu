<?php

namespace Katu\Cache;

class AdapterCollection extends \ArrayObject
{
	public function filterWithoutMemory(): AdapterCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Adapter $adapter) {
			return !$adapter->isMemory();
		})));
	}
}
