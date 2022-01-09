<?php

namespace Katu\Tools\Strings;

class Replacement
{
	protected $key;
	protected $value;

	public function __construct(string $key, string $value)
	{
		$this->key = $key;
		$this->value = $value;
	}
}
