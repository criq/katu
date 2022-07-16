<?php

namespace Katu\Tools\Events;

use Katu\Types\TIdentifier;

class Listener
{
	protected $eventNamePatterns = [];
	protected $callable;

	public function __construct(?string $eventNamePattern = null, ?callable $callable = null)
	{
		$this->setEventNamePattern($eventNamePattern);
		$this->setCallable($callable);
	}

	public function setEventNamePattern(?string $eventNamePattern): Listener
	{
		$this->setEventNamePatterns(array_values(array_filter(array_map("trim", preg_split("/[\s,]+/", $eventNamePattern)))));

		return $this;
	}

	public function setEventNamePatterns(?array $eventNamePatterns): Listener
	{
		$this->eventNamePatterns = $eventNamePatterns;

		return $this;
	}

	public function getEventNamePatterns(): ?array
	{
		return $this->eventNamePatterns;
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
		foreach ($this->getEventNamePatterns() as $eventNamePattern) {
			$eventNamePatternRegex = strtr($eventNamePattern, [
				".+" => "(\.[a-z0-9]+)",
				".*" => "(\.[a-z0-9]+)*",
			]);
			$eventNamePatternRegex = "/^{$eventNamePatternRegex}$/i";

			var_dump($eventNamePatternRegex);
			var_dump($eventName);
			var_dump(preg_match($eventNamePatternRegex, $eventName));
		}

		return $eventName == $this->getEventNamePatterns();
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
