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

	public static function createFromArray(array $array)
	{
		return new static(\Katu\Files\Formats\JSON::encodeStandard($array));
	}

	public function getString() : string
	{
		return $this->json;
	}

	public function getArray()
	{
		return \Katu\Files\Formats\JSON::decodeAsArray($this);
	}
}
