<?php

namespace Katu\Tools\Security;

class Password
{
	protected $delimiter = '$';
	protected $algo = 'sha512';
	protected $password;
	protected $saltLength = 64;
	protected $salt;

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

		return $password;
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

	public function setDelimiter(string $delimiter) : Password
	{
		if (strlen($delimiter) != 1) {
			throw new \Katu\Exceptions\InputErrorException("Invalid delimiter $delimiter.");
		}

		$this->delimiter = $delimiter;

		return $this;
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

	public function getHashable() : string
	{
		return implode([
			$this->getPassword(),
			$this->getSalt(),
		]);
	}

	public function getHash() : string
	{
		return hash($this->getAlgo(), $this->getHashable());
	}

	public function getEncoded() : string
	{
		return implode([
			$this->getDelimiter(),
			implode($this->getDelimiter(), [
				$this->getAlgo(),
				$this->getSalt(),
				$this->getHash(),
			]),
		]);
	}
}
