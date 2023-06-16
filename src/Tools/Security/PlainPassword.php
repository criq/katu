<?php

namespace Katu\Tools\Security;

class PlainPassword
{
	protected $algo = "sha512";
	protected $iterations = 3;
	protected $plain;
	protected $salt;
	protected $saltLength = 64;

	public function __construct(string $plain)
	{
		$this->setPlain($plain);
	}

	public function __toString(): string
	{
		return $this->getPlain();
	}

	public function setPlain(string $plain): PlainPassword
	{
		$this->plain = $plain;

		return $this;
	}

	public function getPlain(): string
	{
		return $this->plain;
	}

	public function setAlgo(string $algo): PlainPassword
	{
		$this->algo = $algo;

		return $this;
	}

	public function getAlgo(): string
	{
		return $this->algo;
	}

	public function setSaltLength(int $saltLength): PlainPassword
	{
		$this->saltLength = $saltLength;

		return $this;
	}

	public function getSaltLength(): int
	{
		return $this->saltLength;
	}

	public function setSalt(string $salt): PlainPassword
	{
		$this->salt = $salt;

		return $this;
	}

	public function getSalt(): string
	{
		if (is_null($this->salt)) {
			$this->salt = \Katu\Tools\Random\Generator::getString($this->getSaltLength());
		}

		return $this->salt;
	}

	public function setIterations(int $iterations): PlainPassword
	{
		$this->iterations = $iterations;

		return $this;
	}

	public function getIterations(): int
	{
		return $this->iterations;
	}

	public function getHash(): string
	{
		$iteration = 1;

		do {
			$hashable = implode([
				$this->getSalt(),
				$this->getPlain(),
			]);

			$hash = hash($this->getAlgo(), $hashable);
		} while (++$iteration <= $this->getIterations());

		return $hash;
	}

	public function getEncodedPassword(): EncodedPassword
	{
		return new EncodedPassword(\Katu\Files\Formats\JSON::encodeInline([
			"algo" => $this->getAlgo(),
			"salt" => $this->getSalt(),
			"iterations" => $this->getIterations(),
			"hash" => $this->getHash(),
		]));
	}

	public function setupFromEncodedPassword(EncodedPassword $encodedPassword): PlainPassword
	{
		return $this
			->setAlgo($encodedPassword->getAlgo())
			->setSalt($encodedPassword->getSalt())
			->setSaltLength($encodedPassword->getSaltLength())
			->setIterations($encodedPassword->getIterations())
			;
	}
}
