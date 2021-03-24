<?php

namespace Katu\Tools\Security;

class EncryptedString
{
	const DEFAULT_METHOD = 'aes-128-ctr';

	protected $method;
	protected $iv;
	protected $encrypted;

	public function __construct(string $method, string $iv, string $encrypted)
	{
		$this->method = $method;
		$this->iv = bin2hex($iv);
		$this->encrypted = $encrypted;
	}

	public static function encrypt(string $payload) : EncryptedString
	{
		$method = static::DEFAULT_METHOD;
		$iv = hex2bin(substr(sha1($payload), 0, 32));
		$result = openssl_encrypt($payload, $method, \Katu\Config\Config::get('encryption', 'key'), 0, $iv);

		return new EncryptedString($method, $iv, $result);
	}

	public function getPayload() : string
	{
		return openssl_decrypt($this->encrypted, $this->method, \Katu\Config\Config::get('encryption', 'key'), 0, hex2bin($this->iv));
	}
}
