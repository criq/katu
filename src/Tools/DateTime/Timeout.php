<?php

namespace Katu\Tools\DateTime;

use Katu\Types\TSeconds;

class Timeout
{
	protected $timeout;

	public function __construct($timeout)
	{
		$this->timeout = $timeout;
	}

	public function getSeconds() : TSeconds
	{
		if (is_int($this->timeout)) {
			return new TSeconds(abs($this->timeout) * -1);
		} elseif (is_float($this->timeout)) {
			return new TSeconds(abs($this->timeout) * -1);
		} elseif (is_string($this->timeout)) {
			return (new \Katu\Tools\DateTime\DateTime('- ' . $this->timeout))->getAge();
		}

		throw new \Katu\Exceptions\InputErrorException("Invalid timeout.");
	}

	public function getDateTime() : DateTime
	{
		return new DateTime($this->getSeconds()->getValue() . ' seconds');
	}
}
