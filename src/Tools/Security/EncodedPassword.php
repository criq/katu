<?php

namespace Katu\Tools\Security;

class EncodedPassword
{
	protected $encoded;

	public function __construct(?string $encoded = null)
	{
		$this->setEncoded($encoded);
	}

	public function __toString(): string
	{
		return $this->getEncoded();
	}

	public function setEncoded(string $encoded): EncodedPassword
	{
		$this->encoded = $encoded;

		return $this;
	}

	public function getEncoded(): string
	{
		return $this->encoded;
	}

	public function getArray(): array
	{
		return \Katu\Files\Formats\JSON::decodeAsArray($this->getEncoded());
	}

	public function getAlgo(): string
	{
		return $this->getArray()["algo"];
	}

	public function getSalt(): string
	{
		return $this->getArray()["salt"];
	}

	public function getSaltLength(): int
	{
		return strlen($this->getSalt());
	}

	public function getIterations(): int
	{
		return $this->getArray()["iterations"];
	}

	public function validatePlainPassword(PlainPassword $plainPassword): bool
	{
		return $plainPassword->setupFromEncodedPassword($this)->getEncodedPassword() == $this->getEncoded();
	}
}
