<?php

namespace Katu\Tools\Emails;

class VariableCollection extends \ArrayObject
{
	public function getAssoc(): array
	{
		return array_combine(
			array_map(function (Variable $variable) {
				return $variable->getKey();
			}, $this->getArrayCopy()),
			array_map(function (Variable $variable) {
				return $variable->getValue();
			}, $this->getArrayCopy()),
		);
	}
}
