<?php

namespace Katu\Tools\DateTime;

class Timeout
{
	protected $timeout;

	public function __construct(mixed $timeout)
	{
		$this->timeout = $timeout;
	}

	public function getSeconds() : int
	{
		if (is_int($this->timeout)) {
			return $this->timeout;
		} elseif (is_float($this->timeout)) {
			return round($this->timeout);
		} elseif (is_string($this->timeout)) {
			return (new \Katu\Tools\DateTime\DateTime('- ' . $this->timeout))->getAge();
		}

		throw new \Katu\Exceptions\InputErrorException("Invalid timeout.");
	}

	public function getDateTime() : DateTime
	{
		return new DateTime('- ' . $this->getSeconds() . ' seconds');
	}
}
