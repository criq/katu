<?php

namespace Katu\Tools\Events;

use Katu\Types\TIdentifier;

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

	public function matchesEventName(string $eventName): bool
	{
		return $eventName == $this->getEventPattern();
	}

	public function runWithEvent(Event $event): bool
	{
		try {
			call_user_func_array($this->getCallable(), [$event]);

			return true;
		} catch (\Throwable $e) {
			\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->error($e);

			return false;
		}
	}
}
