<?php

namespace Katu\Cache;

use Katu\Tools\Calendar\Seconds;
use Katu\Tools\Calendar\Timeout;
use Katu\Types\TIdentifier;

class Pickle
{
	const DIR_NAME = "pickles";

	protected $identifier;

	public function __construct(TIdentifier $identifier)
	{
		$this->setIdentifier($identifier);
	}

	public function setIdentifier(TIdentifier $identifier): Pickle
	{
		$this->identifier = $identifier;

		return $this;
	}

	public function getIdentifier(): TIdentifier
	{
		return $this->identifier;
	}

	public function getFile(): \Katu\Files\File
	{
		return new \Katu\Files\File(\App\App::getTemporaryDir(), static::DIR_NAME, $this->getIdentifier()->getPath("txt"));
	}

	public function get()
	{
		return unserialize($this->getFile()->get());
	}

	public function set($value): Pickle
	{
		$this->getFile()->set(serialize($value));

		return $this;
	}

	public function delete()
	{
		return $this->getFile()->delete();
	}

	public function getModifiedTime(): ?\Katu\Tools\Calendar\Time
	{
		return $this->getFile()->getModifiedTime() ?: null;
	}

	public function hasContents(): bool
	{
		return !is_null($this->get());
	}

	public function getAge(): ?Seconds
	{
		try {
			return $this->getFile()->getModifiedTime()->getAge();
		} catch (\Throwable $e) {
			return null;
		}
	}

	public function isExpired(Timeout $timeout): bool
	{
		try {
			return (bool)(!$this->hasContents() || !$timeout->fits($this->getModifiedTime()));
		} catch (\Throwable $e) {
			return true;
		}
	}

	public function isValid(?Timeout $timeout = null): bool
	{
		if ($timeout && $this->isExpired($timeout)) {
			return false;
		}

		return $this->hasContents();
	}

	public function getOrCreate(Timeout $timeout, callable $callback)
	{
		if ($this->isExpired($timeout)) {
			$this->set(call_user_func_array($callback, array_slice(func_get_args(), 2)));
		}

		return $this->get();
	}
}
