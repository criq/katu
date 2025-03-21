<?php

namespace Katu\Tools\Security;

use App\Config\AppConfig;
use App\Config\TimeConfig;
use Lcobucci\JWT\Configuration;

class JWT
{
	private $config;

	public function __construct()
	{
		$this->setConfig(Configuration::forSymmetricSigner(
			new \Lcobucci\JWT\Signer\Hmac\Sha256,
			\Lcobucci\JWT\Signer\Key\InMemory::plainText(\App\App::getEncryptionConfig()->getKey()),
		));
		$this->getConfig()->setValidationConstraints(
			new \Lcobucci\JWT\Validation\Constraint\SignedWith($this->getConfig()->signer(), $this->getConfig()->signingKey()),
			new \Lcobucci\JWT\Validation\Constraint\LooseValidAt(new \Lcobucci\Clock\SystemClock($this->getTimezone())),
		);
	}

	public function getTimezone(): \DateTimeZone
	{
		return \App\App::getTimeConfig()->getTimezone();
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
			->issuedBy(\App\App::getAppConfig()->getBaseURL())
			->issuedAt(new \DateTimeImmutable("now", $this->getTimezone()))
			->expiresAt($expiresAt)
			;

		foreach ($claims as $key => $value) {
			$builder->withClaim($key, $value);
		}

		return $builder->getToken($this->getConfig()->signer(), $this->getConfig()->signingKey());
	}
}
