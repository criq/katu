<?php

namespace Katu\Types\Encryption;

use Katu\Types\TJSON;

class TEncryptedStringJSONStorableString
{
	protected $string;

	public function __construct(string $string)
	{
		$this->string = $string;
	}

	public function __toString() : string
	{
		return $this->string;
	}

	public static function createFromJSON(TJSON $json)
	{
		return new static(bin2hex(gzencode($json, 9)));
	}

	public function getJSON() : TEncryptedStringJSON
	{
		return new TEncryptedStringJSON(new TJSON(gzdecode(hex2bin($this->string))));
	}
}