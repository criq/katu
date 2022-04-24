<?php

namespace Katu\PDO;

class Name extends \Sexy\Expression
{
	public $plain;

	public function __construct(string $plain)
	{
		$this->setPlain($plain);
	}

	public function __toString()
	{
		return $this->getSql();
	}

	public function setPlain(string $value): Name
	{
		$this->plain = $value;

		return $this;
	}

	public function getPlain(): string
	{
		return $this->plain;
	}

	public function getSql(&$context = [])
	{
		return "`{$this->getPlain()}`";
	}
}
