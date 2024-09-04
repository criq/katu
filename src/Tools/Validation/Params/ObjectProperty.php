<?php

namespace Katu\Tools\Validation\Params;

use Katu\Tools\Validation\Param;

class ObjectProperty extends Param
{
	protected $property;

	public function setProperty(string $value): ObjectProperty
	{
		$this->property = trim($value);

		return $this;
	}

	public function getProperty(): ?string
	{
		return $this->property;
	}
}
