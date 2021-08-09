<?php

namespace Katu\Types\Encryption;

use Katu\Types\TJSON;

class TEncryptedStringJSON
{
	protected $json;

	public function __construct(TJSON $json)
	{
		$this->json = $json;
	}

	public function getEncryptedString() : TEncryptedString
	{
		$array = \Katu\Files\Formats\JSON::decodeAsArray($this->json);

		return new TEncryptedString($array['method'], hex2bin($array['ivHex']), $array['encrypted']);
	}

	public function getPortableString() : TEncryptedStringJSONPortableString
	{
		return TEncryptedStringJSONPortableString::createFromJSON($this->json);
	}
}
