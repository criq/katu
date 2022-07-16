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

	public function trigger(string $name, array $args = [])
	{
		$event = new Event($name, $args);

		return $this->triggerEvent($event);
	}

	public function triggerEvent(Event $event)
	{
		$listeners = $this->getEventListeners($event);
		var_dump($listeners);

		foreach ($listeners as $listener) {
			$listener->runWithEvent($event);
		}
	}

	public function getEventListeners(Event $event): ListenerCollection
	{
		return $this->getListeners()->filterForEventName($event->getName());
	}
}
