<?php

namespace Katu\Tools\Events;

class ListenerCollection extends \ArrayObject
{
	public function filterForEventName(string $eventName)
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Listener $listener) use ($eventName) {
			var_dump($eventName);
			var_dump($listener->getEventPatterns());

			return $listener->matchesEventName($eventName);
		})));
	}
}
