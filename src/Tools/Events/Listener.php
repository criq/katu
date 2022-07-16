<?php

namespace Katu\Tools\Events;

use Katu\Types\TIdentifier;

class Listener
{
	protected $patterns;
	protected $callable;

	public function __construct(?string $pattern = null, ?callable $callable = null)
	{
		$this->setPattern($pattern);
		$this->setCallable($callable);
	}

	public function setPattern(?string $pattern): Listener
	{
		$this->setPatterns(PatternCollection::createFromString($pattern));

		return $this;
	}

	public function setPatterns(PatternCollection $patterns): Listener
	{
		$this->patterns = $patterns;

		return $this;
	}

	public function getPatterns(): PatternCollection
	{
		if (!$this->patterns) {
			$this->patterns = new PatternCollection;
		}

		return $this->patterns;
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
		foreach ($this->getPatterns() as $pattern) {
			if ($pattern->matches($eventName)) {
				return true;
			}
		}

		return false;
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
