<?php

namespace Katu\Tools\Events;

use Katu\Types\TIdentifier;

class Listener
{
	protected $eventPatterns = [];
	protected $callable;

	public function __construct(?string $eventPattern = null, ?callable $callable = null)
	{
		$this->setEventPattern($eventPattern);
		$this->setCallable($callable);
	}

	public function setEventPattern(?string $eventPattern): Listener
	{
		$this->setEventPatterns(array_values(array_filter(array_map("trim", preg_split("/[\s,]+/", $eventPattern)))));

		return $this;
	}

	public function setEventPatterns(?array $eventPatterns): Listener
	{
		$this->eventPatterns = $eventPatterns;

		return $this;
	}

	public function getEventPatterns(): ?array
	{
		return $this->eventPatterns;
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
		return $eventName == $this->getEventPatterns();
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
