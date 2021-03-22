<?php

namespace Katu\Tools\Security;

class Encryption
{
	protected $method = 'aes-128-ctr';

	public function getEncrypted(string $data)
	{
		$iv = openssl_random_pseudo_bytes(16);
		$result = openssl_encrypt($data, $this->method, \Katu\Config\Config::get('encryption', 'key'), 0, $iv);

		return new Encrypted($this->method, $iv, $result);
	}
}
