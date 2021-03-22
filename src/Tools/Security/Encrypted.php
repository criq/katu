<?php

namespace Katu\Tools\Security;

class Encrypted
{
	protected $method;
	protected $iv;
	protected $data;

	public function __construct(string $method, string $iv, string $data)
	{
		$this->method = $method;
		$this->iv = bin2hex($iv);
		$this->data = $data;
	}

	public function __toString() : string
	{
		return \Katu\Files\Formats\JSON::encodeInline([
			'method' => $this->method,
			'iv' => $this->iv,
			'data' => $this->data,
		]);
	}

	public function getDecrypted()
	{
		return openssl_decrypt($this->data, $this->method, \Katu\Config\Config::get('encryption', 'key'), 0, hex2bin($this->iv));
	}
}
