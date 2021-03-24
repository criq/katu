<?php

namespace Katu\Types;

class TPackage
{
	protected $payload;

	public function __construct(array $payload)
	{
		$this->payload = $payload;
	}

	public function __toString() : string
	{
		return $this->getString();
	}

	public static function createFromString(string $string) : TPackage
	{
		$encodedString = new \Katu\Tools\Security\EncodedString($string);

		return static::createFromJSON(new \Katu\Types\TJSON($encodedString->getPayload()));
	}

	public static function createFromJSON(\Katu\Types\TJSON $json) : TPackage
	{
		return new static(\Katu\Files\Formats\JSON::decodeAsArray($json));
	}

	public function getPayload() : array
	{
		return $this->payload;
	}

	public function getJSON() : \Katu\Types\TJSON
	{
		return new \Katu\Types\TJSON(\Katu\Files\Formats\JSON::encodeInline($this->payload));
	}

	public function getString() : string
	{
		return $this->getEncodedString()->getEncoded();
	}

	public function getEncodedString() : \Katu\Tools\Security\EncodedString
	{
		return \Katu\Tools\Security\EncodedString::encode($this->getJSON()->getString());
	}
}
