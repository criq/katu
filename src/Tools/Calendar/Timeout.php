<?php

namespace Katu\Tools\Calendar;

class Timeout
{
	protected $timeout;

	public function __construct($timeout)
	{
		$this->setTimeout($timeout);
	}

	public function setTimeout(string $value): Timeout
	{
		$this->timeout = $value;

		return $this;
	}

	public function getTimeout(): string
	{
		return $this->timeout;
	}

	public function getSeconds(): Seconds
	{
		if (is_numeric($this->getTimeout())) {
			return new Seconds(abs($this->getTimeout()) * -1);
		} elseif (is_string($this->getTimeout())) {
			return (new Time("- {$this->getTimeout()}"))->getAge();
		}

		throw new \Katu\Exceptions\InputErrorException("Invalid timeout.");
	}

	public function getTime(): Time
	{
		return new Time("{$this->getSeconds()->getValue()} seconds");
	}

	public function fits(\DateTime $datetime): bool
	{
		return $this->getTime()->getTimestamp() <= $datetime->getTimestamp();
	}
}
