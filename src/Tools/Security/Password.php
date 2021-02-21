<?php

namespace Katu\Tools\Security;

class Password
{
	protected $delimiter = '$';
	protected $algo = 'sha512';
	protected $saltLength = 64;
	protected $salt;
	protected $iterations = 3;
	protected $password;

	public function __construct(?string $password = null)
	{
		$this->password = $password;
	}

	public static function createFromEncoded(string $encoded) : Password
	{
		$password = new static;
		$password->setDelimiter(substr($encoded, 0, 1));

		$e = explode($password->getDelimiter(), substr($encoded, 1));
		$password->setAlgo($e[0]);
		$password->setSaltLength(strlen($e[1]));
		$password->setSalt($e[1]);
		$password->setIterations((int)$e[2] ? (int)$e[2] : 1);

		return $password;
	}

	public function setDelimiter(string $delimiter) : Password
	{
		if (strlen($delimiter) != 1) {
			throw new \Katu\Exceptions\InputErrorException("Invalid delimiter $delimiter.");
		}

		$this->delimiter = $delimiter;

		return $this;
	}

	public function setAlgo(string $algo) : Password
	{
		if (!in_array($algo, hash_algos())) {
			throw new \Katu\Exceptions\InputErrorException("Invalid hashing algorithm $algo.");
		}

		$this->algo = $algo;

		return $this;
	}

	public function getAlgo() : string
	{
		return $this->algo;
	}

	public function setSaltLength(int $saltLength) : Password
	{
		$this->saltLength = $saltLength;

		return $this;
	}

	public function setSalt(string $salt) : Password
	{
		$this->salt = $salt;

		return $this;
	}

	public function getSalt() : string
	{
		if (!$this->salt) {
			$this->salt = \Katu\Tools\Random\Generator::getString($this->saltLength);
		}

		return $this->salt;
	}

	public function setIterations(int $iterations) : Password
	{
		$this->iterations = $iterations;

		return $this;
	}

	public function getIterations() : int
	{
		return $this->iterations;
	}

	public function setPassword(string $password) : Password
	{
		$this->password = $password;

		return $this;
	}

	public function getPassword() : string
	{
		return $this->password;
	}

	public function getDelimiter() : string
	{
		return $this->delimiter;
	}

	public function getHash() : string
	{
		$iteration = 1;
		$password = $this->getPassword();

		do {
			$hashable = implode([
				$this->getSalt(),
				$password,
			]);

			$hash = $password = hash($this->getAlgo(), $hashable);
		} while (++$iteration <= $this->iterations);

		return $hash;
	}

	public function getEncoded() : string
	{
		return implode([
			$this->getDelimiter(),
			implode($this->getDelimiter(), [
				$this->getAlgo(),
				$this->getSalt(),
				$this->getIterations(),
				$this->getHash(),
			]),
		]);
	}
}
