<?php

namespace Katu\Cache;

use Katu\Types\TIdentifier;

class Runtime
{
	protected $args = [];
	protected $callback;
	protected $identifier;
	protected static $cache = [];

	public function __construct(TIdentifier $identifier, ?callable $callback = null)
	{
		$this->setIdentifier($identifier);
		$this->setCallback($callback);
		$this->setArgs(...array_slice(func_get_args(), 2));
	}

	public function setIdentifier(TIdentifier $identifier) : Runtime
	{
		$this->identifier = $identifier;

		return $this;
	}

	public function getIdentifier() : TIdentifier
	{
		return $this->identifier;
	}

	public function getIdentifierWithArgs() : TIdentifier
	{
		return new TIdentifier(...array_merge($this->getIdentifier()->getParts(), $this->getArgs()));
	}

	public function setCallback(?callable $callback) : Runtime
	{
		$this->callback = $callback;

		return $this;
	}

	public function getCallback() : ?callable
	{
		return $this->callback;
	}

	public function setArgs() : Runtime
	{
		$this->args = func_get_args();

		return $this;
	}

	public function getArgs() : array
	{
		return $this->args;
	}

	public function getKey() : string
	{
		return $this->getIdentifierWithArgs()->getKey();
	}

	public function getResult()
	{
		$key = $this->getKey();

		// There's something cached.
		if (isset(static::$cache[(string)$key]) && !is_null(static::$cache[(string)$key])) {
			return static::$cache[(string)$key];
		}

		// There is callback.
		if (!is_null($this->getCallback())) {
			static::$cache[(string)$key] = call_user_func_array($this->getCallback(), $this->getArgs());
			return static::$cache[(string)$key];
		}

		return null;
	}

	public static function get(TIdentifier $identifier, ?callable $callback = null)
	{
		$cache = new static($identifier, $callback);
		$cache->setArgs(...array_slice(func_get_args(), 2));

		return $cache->getResult();
	}

	public static function clear()
	{
		return static::$cache = [];
	}
}
