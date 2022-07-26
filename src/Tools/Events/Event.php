<?php

namespace Katu\Tools\Events;

class Event
{
	protected $name;
	protected $args = [];

	public function __construct(string $name, array $args = [])
	{
		$this->setName($name);
		$this->setArgs($args);
	}

	public function setName(string $name): Event
	{
		$this->name = $name;

		return $this;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setArgs(array $args = []): Event
	{
		$this->args = $args;

		return $this;
	}

	public function getArgs(): array
	{
		return $this->args ?: [];
	}

	public function getArg(string $key)
	{
		return $this->getArgs()[$key] ?? null;
	}
}
