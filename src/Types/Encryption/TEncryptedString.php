<?php

namespace Katu\Types\Encryption;

use Katu\Types\TJSON;

class TEncryptedString
{
	const DEFAULT_METHOD = "aes-256-ctr";

	protected $method;
	protected $iv;
	protected $encrypted;

	public function __construct(string $method, string $iv, string $encrypted)
	{
		$this->method = $method;
		$this->iv = $iv;
		$this->encrypted = $encrypted;
	}

	public static function getDefaultMethod(): string
	{
		return static::DEFAULT_METHOD;
	}

	public static function generateIv(string $original): string
	{
		try {
			$salt = \Katu\Config\Config::get("encryption", "salt");
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			$salt = null;
		}

		return hex2bin(substr(sha1(sha1(implode([
			$salt,
			$original,
		]))), 0, 32));
	}

	public static function encrypt(string $original, ?string $iv = null): TEncryptedString
	{
		$method = static::getDefaultMethod();
		$key = \Katu\Config\Config::get("encryption", "key");
		$iv = $iv ?: static::generateIv($original);

		$result = openssl_encrypt($original, $method, $key, 0, $iv);

		return new static($method, $iv, $result);
	}

	public function getEncrypted(): string
	{
		return $this->encrypted;
	}

	public function getJSON(): TEncryptedStringJSON
	{
		return new TEncryptedStringJSON(new TJSON(\Katu\Files\Formats\JSON::encodeInline([
			"method" => $this->method,
			"ivHex" => bin2hex($this->iv),
			"encrypted" => $this->encrypted,
		])));
	}

	public function getOriginal(): string
	{
		return openssl_decrypt($this->encrypted, $this->method, \Katu\Config\Config::get("encryption", "key"), 0, $this->iv);
	}
}
