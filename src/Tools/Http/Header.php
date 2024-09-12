<?php

namespace Katu\Tools\HTTP;

class Header
{
	protected $name;
	protected $value;

	public function __construct(string $name, ?string $value)
	{
		$this->name = $name;
		$this->value = $value;
	}
}
