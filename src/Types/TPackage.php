<?php

namespace Katu\Types;

use Katu\Types\Encryption\TEncryptedString;
use Katu\Types\Encryption\TEncryptedStringJSONStorableString;

class TPackage
{
	protected $payload;

	public function __construct(array $payload)
	{
		$this->payload = $payload;
	}

	public function __toString() : string
	{
		return $this->getStorableString();
	}

	public static function createFromStorableString(string $string) : TPackage
	{
		return new static(\Katu\Files\Formats\JSON::decodeAsArray((new TEncryptedStringJSONStorableString($string))->getJSON()->getEncryptedString()->getOriginal()));
	}

	public function getPayload() : array
	{
		return $this->payload;
	}

	public function getJSON() : TJSON
	{
		return new TJSON(\Katu\Files\Formats\JSON::encodeInline($this->payload));
	}

	public function getStorableString() : TEncryptedStringJSONStorableString
	{
		return TEncryptedString::encrypt($this->getJSON())->getJSON()->getStorableString();
	}
}
