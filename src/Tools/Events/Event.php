<?php

namespace Katu\Tools\Events;

class Event
{
	protected $name;

	public function __construct(string $name)
	{
		$this->name = $name;
	}
}
