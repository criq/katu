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

	public function trigger(Event $event)
	{
		var_dump($event);
	}
}
