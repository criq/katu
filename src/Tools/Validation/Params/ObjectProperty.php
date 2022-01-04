<?php

namespace Katu\Tools\Validation\Params;

class ObjectProperty extends \Katu\Tools\Validation\Param
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
