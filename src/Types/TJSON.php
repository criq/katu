<?php

namespace Katu\Types;

class TJSON
{
	public $json;

	public function __construct(string $json)
	{
		$this->json = $json;
	}

	public function __toString() : string
	{
		return $this->getString();
	}

	public function getString() : string
	{
		return $this->json;
	}
}
