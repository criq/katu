<?php

namespace Katu\Types;

class TJSON
{
	public $json;

	public function __construct(string $json)
	{
		$this->json = $json;
	}

	public function __toString(): string
	{
		return $this->getString();
	}

	public static function createFromContents($contents): TJSON
	{
		return new static(\Katu\Files\Formats\JSON::encodeStandard($contents));
	}

	public function getString(): string
	{
		return $this->json;
	}

	public function getDecoded()
	{
		return \Katu\Files\Formats\JSON::decodeAsArray($this);
	}

	public function getArray(): array
	{
		return \Katu\Files\Formats\JSON::decodeAsArray($this);
	}
}
