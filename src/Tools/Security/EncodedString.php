<?php

namespace Katu\Tools\Security;

class EncodedString
{
	protected $encoded;

	public function __construct(string $encoded)
	{
		$this->encoded = $encoded;
	}

	public static function encode(string $string) : EncodedString
	{
		$encryptedString = \Katu\Tools\Security\EncryptedString::encrypt($string);

		return new static(base64_encode(gzencode(serialize($encryptedString))));
	}

	public function getEncoded()
	{
		return $this->encoded;
	}

	public function getPayload() : string
	{
		$encryptedString = unserialize(gzdecode(base64_decode($this->getEncoded())));

		return $encryptedString->getPayload();
	}
}
