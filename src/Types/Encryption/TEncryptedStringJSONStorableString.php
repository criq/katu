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

	public static function createFromJSON(TJSON $json)
	{
		return new static(bin2hex(gzencode($json)));
	}

	public function getJSON() : TEncryptedStringJSON
	{
		return new TEncryptedStringJSON(new TJSON(gzdecode(hex2bin($this->string))));
	}
}
