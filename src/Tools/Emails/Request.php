<?php

namespace Katu\Tools\Emails;

class Request
{
	protected $provider;
	protected $email;

	public function __construct(Provider $provider, Email $email)
	{
		$this->setProvider($provider);
		$this->setEmail($email);
	}

	public function setProvider(Provider $provider): Request
	{
		$this->provider = $provider;

		return $this;
	}

	public function getProvider(): Provider
	{
		return $this->provider;
	}

	public function setEmail(Email $email): Request
	{
		$this->email = $email;

		return $this;
	}

	public function getEmail(): Email
	{
		return $this->email;
	}

	public function createResponse(): Response
	{
		return $this->getProvider()->dispatch($this);
	}
}
