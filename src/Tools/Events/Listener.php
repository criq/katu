<?php

namespace Katu\Tools\Events;

class Listener
{
	protected $eventPattern;
	protected $callable;

	public function __construct(?string $eventPattern = null, ?callable $callable = null)
	{
		$this->setEventPattern($eventPattern);
		$this->setCallable($callable);
	}

	public function setEventPattern(?string $eventPattern): Listener
	{
		$this->eventPattern = $eventPattern;

		return $this;
	}

	public function getEventPattern(): ?string
	{
		return $this->eventPattern;
	}

	public function setCallable(?callable $callable): Listener
	{
		$this->callable = $callable;

		return $this;
	}

	public function getCallable(): ?callable
	{
		return $this->callable;
	}
}
