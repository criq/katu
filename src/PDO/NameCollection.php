<?php

namespace Katu\PDO;

class NameCollection extends \ArrayObject
{
	public function getPlain(): array
	{
		return array_map(function (Name $name) {
			return $name->getPlain();
		}, $this->getArrayCopy());
	}
}
