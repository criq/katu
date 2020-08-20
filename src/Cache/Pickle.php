<?php

namespace Katu\Cache;

class Pickle
{
	protected $name;

	public function __construct($name, $value = null)
	{
		$this->name = $name;

		if (!$this->getFile()->exists()) {
			$this->set(null);
		}

		if ($value) {
			$this->set($value);
		}
	}

	public function getName()
	{
		return $this->name;
	}

	public function getFile()
	{
		return new \Katu\Files\File(\Katu\App::getTemporaryDir(), 'pickles', \Katu\Files\File::generatePath($this->name, 'txt'));
	}

	public function get()
	{
		return unserialize($this->getFile()->get());
	}

	public function set($value)
	{
		return $this->getFile()->set(serialize($value));
	}

	public function delete()
	{
		return $this->getFile()->delete();
	}

	public function getDateTimeModified()
	{
		return \Katu\Tools\DateTime\DateTime::createFromTimestamp(filemtime($this->getFile()));
	}

	public function hasContents() : bool
	{
		return !is_null($this->get());
	}

	public function isExpired($timeout) : bool
	{
		return (bool)(is_null($this->get()) || !$this->getDateTimeModified()->isInTimeout(\Katu\Cache\General::parseTimeout($timeout)));
	}

	public function isValid($timeout = null) : bool
	{
		if ($timeout && $this->isExpired($timeout)) {
			return false;
		}

		return $this->hasContents();
	}

	public function getOrCreate($timeout, callable $callback)
	{
		if ($this->isExpired($timeout)) {
			$this->set(call_user_func_array($callback, array_slice(func_get_args(), 2)));
		}

		return $this->get();
	}
}
