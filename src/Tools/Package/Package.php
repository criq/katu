<?php

namespace Katu\Tools\Package;

use Katu\Types\Encryption\TEncryptedString;
use Katu\Types\Encryption\TEncryptedStringJSONPortableString;
use Katu\Types\TJSON;

class Package implements \JsonSerializable, \Stringable
{
	protected $payload;

	public function __construct(array $payload)
	{
		$this->payload = $payload;
	}

	public function __toString(): string
	{
		return $this->getPortableString();
	}

	public static function createFromJSON(TJSON $json): Package
	{
		return new static($json->getArray());
	}

	public static function createFromPortableString(string $string): Package
	{
		$original = (new TEncryptedStringJSONPortableString($string))->getJSON()->getEncryptedString()->getOriginal();

		return static::createFromJSON(new TJSON($original));
	}

	public function getPayload(): array
	{
		return $this->payload;
	}

	public function getJSON(): TJSON
	{
		return TJSON::createFromContents($this->payload);
	}

	public function getHash(): string
	{
		return sha1($this->getJSON());
	}

	public function getPortableString(): TEncryptedStringJSONPortableString
	{
		return TEncryptedString::encrypt($this->getJSON())->getJSON()->getPortableString();
	}

	public function jsonSerialize()
	{
		return $this->getPayload();
	}
}
