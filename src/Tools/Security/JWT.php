<?php

namespace Katu\Tools\Security;

use Lcobucci\JWT\Configuration;

class JWT
{
	public function __construct()
	{
		$this->setConfig(Configuration::forSymmetricSigner(
			new \Lcobucci\JWT\Signer\Hmac\Sha256,
			\Lcobucci\JWT\Signer\Key\InMemory::plainText(\Katu\Config\Config::get("encryption", "key")),
		));
		$this->getConfig()->setValidationConstraints(
			new \Lcobucci\JWT\Validation\Constraint\SignedWith($this->getConfig()->signer(), $this->getConfig()->signingKey()),
			new \Lcobucci\JWT\Validation\Constraint\LooseValidAt(new \Lcobucci\Clock\SystemClock($this->getTimezone())),
		);

		// $token = $config->parser()->parse($string);
		// var_dump($token);
		// var_dump($token->claims());
		// var_dump($token->claims()->get("uid"));

		// // var_dump($config->validationConstraints());
		// try {
		// 	var_dump($config->validator()->assert($token, ...$config->validationConstraints()));
		// } catch (\Throwable $e) {
		// 	var_dump($e);
		// }
	}

	public function getTimezone(): \DateTimeZone
	{
		return new \DateTimeZone(\Katu\Config\Config::get("app", "timezone"));
	}

	public function setConfig(Configuration $config): JWT
	{
		$this->config = $config;

		return $this;
	}

	public function getConfig(): Configuration
	{
		return $this->config;
	}

	public function createToken(\DateTimeImmutable $expiresAt, array $claims): \Lcobucci\JWT\Token\Plain
	{
		$builder = $this->getConfig()->builder()
			->issuedBy(\Katu\Config\Config::get("app", "baseUrl"))
			->issuedAt(new \DateTimeImmutable("now", $this->getTimezone()))
			->expiresAt($expiresAt)
			;

		foreach ($claims as $key => $value) {
			$builder->withClaim($key, $value);
		}

		return $builder->getToken($this->getConfig()->signer(), $this->getConfig()->signingKey());
	}
}
