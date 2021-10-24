<?php

namespace Katu\Cache;

use Katu\Tools\DateTime\Timeout;
use Katu\Types\TIdentifier;

class General
{
	protected $adapters;
	protected $args = [];
	protected $callback;
	protected $identifier;
	protected $memoryKey;
	protected $timeout;

	public function __construct(TIdentifier $identifier, ?Timeout $timeout = null, ?callable $callback = null)
	{
		$this->setIdentifier($identifier);
		$this->setTimeout($timeout);
		$this->setCallback($callback);
		$this->setArgs(...array_slice(func_get_args(), 3));
		$this->setAdapters(static::getAvailableAdapters());
	}

	public function setIdentifier(TIdentifier $identifier): General
	{
		$this->identifier = $identifier;

		return $this;
	}

	public function getIdentifier(): TIdentifier
	{
		return $this->identifier;
	}

	public function getIdentifierWithArgs(): TIdentifier
	{
		return new TIdentifier(...array_merge($this->getIdentifier()->getParts(), $this->getArgs()));
	}

	public function setTimeout(?Timeout $timeout = null): General
	{
		$this->timeout = $timeout;

		return $this;
	}

	public function getTimeout(): Timeout
	{
		return $this->timeout;
	}

	public function setCallback(?callable $callback): General
	{
		$this->callback = $callback;

		return $this;
	}

	public function getCallback(): ?callable
	{
		return $this->callback;
	}

	public function setArgs(): General
	{
		$this->args = func_get_args();

		return $this;
	}

	public function getArgs(): array
	{
		return $this->args;
	}

	public static function getAvailableAdapters(): AdapterCollection
	{
		return new AdapterCollection([
			new Adapters\Redis,
			new Adapters\Memcached,
			new Adapters\APC,
			new Adapters\File,
		]);
	}

	public function setAdapters(AdapterCollection $adapters): General
	{
		$this->adapters = $adapters;

		return $this;
	}

	public function getAdapters(): AdapterCollection
	{
		return $this->adapters;
	}

	public function getResult()
	{
		foreach ($this->getAdapters() as $adapter) {
			$value = $adapter->get($this->getIdentifierWithArgs(), $this->getTimeout());
			if (!is_null($value)) {
				return $value;
			}
		}

		$value = call_user_func_array($this->getCallback(), $this->getArgs());
		foreach ($this->getAdapters() as $adapter) {
			if ($adapter->set($this->getIdentifierWithArgs(), $this->getTimeout(), $value)) {
				break;
			}
		}

		return $value;
	}

	public function clear()
	{
		foreach ($this->getAdapters() as $adapter) {
			$adapter->delete($this->getIdentifierWithArgs());
		}
	}

	public static function clearMemory()
	{
		foreach (static::getAvailableAdapters() as $adapter) {
			$adapter->flush();
		}
	}

	public function exists(): bool
	{
		foreach ($this->getAdapters() as $adapter) {
			if ($adapter->exists($this->getIdentifier(), $this->getTimeout())) {
				return true;
			}
		}

		return false;
	}

	/****************************************************************************
	 * Code sugar.
	 */
	public static function get(TIdentifier $identifier, Timeout $timeout, ?callable $callback = null)
	{
		$cache = new static($identifier, $timeout, $callback);
		$cache->setArgs(...array_slice(func_get_args(), 3));

		return $cache->getResult();
	}
}
