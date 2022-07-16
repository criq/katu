<?php

namespace Katu\Tools\Events;

class Dispatcher
{
	protected $listeners;

	public function getListeners(): ListenerCollection
	{
		if (!$this->listeners) {
			$this->listeners = new ListenerCollection;
		}

		return $this->listeners;
	}

	public function addListener(Listener $listener): Dispatcher
	{
		$this->getListeners()[] = $listener;

		return $this;
	}

	public function addListeners(iterable $listeners): Dispatcher
	{
		foreach ($listeners as $listener) {
			$this->addListener($listener);
		}

		return $this;
	}

	public function getEventListeners(Event $event): ListenerCollection
	{
		return $this->getListeners()->filterForEventName($event->getName());
	}

	public function trigger(string $name, array $args = [])
	{
		return $this->triggerEvent(new Event($name, $args));
	}

	public function triggerEvent(Event $event)
	{
		foreach ($this->getEventListeners($event) as $listener) {
			$listener->runWithEvent($event);
		}
	}
}
