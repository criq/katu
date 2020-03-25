<?php

namespace Katu\Tools\Keys;

abstract class Key
{
	protected $delimiter = "/";
	protected $hashPrefixCount = 3;
	protected $hashPrefixLength = 2;
	protected $source;

	abstract public function getParts();

	public function __construct($source)
	{
		$this->source = $source;
	}

	public function __toString()
	{
		return $this->getKey();
	}

	public function getKey()
	{
		$key = implode($this->delimiter, $this->getParts());

		return $key;
	}

	public function setDelimiter($delimiter)
	{
		$this->delimiter = $delimiter;

		return $this;
	}

	public function setHashPrefixLength($hashPrefixLength)
	{
		$this->hashPrefixLength = $hashPrefixLength;

		return $this;
	}

	public function setHashPrefixCount($hashPrefixCount)
	{
		$this->hashPrefixCount = $hashPrefixCount;

		return $this;
	}

	public function getHash($arg)
	{
		return sha1(var_export($arg, true));
	}

	public function getHashPrefix($arg)
	{
		return array_slice(str_split($this->getHash($arg), $this->hashPrefixLength), 0, $this->hashPrefixCount);
	}

	public function getHashWithPrefix($arg)
	{
		$array = new \Katu\Types\TArray;
		$array->append($this->getHashPrefix($arg));
		$array->append($this->getHash($arg));

		return $array;
	}

	public function getSanitizedString($string)
	{
		return array_map(function ($i) {
			return (new \Katu\Types\TString($i))->getForUrl([
				'delimiter' => $this->delimiter,
				'lowercase' => true,
			]);
		}, preg_split("/[^\d\pL]/ui", $string));
	}
}
