<?php

namespace Katu\Tools\Events;

class Event
{
	protected $name;
	protected $args;

	public function __construct(string $name, array $args = [])
	{
		$this->name = $name;
		$this->args = $args;
	}
}
