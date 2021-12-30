<?php

namespace Katu\Tools\Validation\Params;

class Property extends \Katu\Tools\Validation\Param
{
	protected $property;

	public function setProperty(string $value): Property
	{
		$this->property = trim($value);

		return $this;
	}

	public function getProperty(): ?string
	{
		return $this->property;
	}
}
