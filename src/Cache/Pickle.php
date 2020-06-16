<?php

namespace Katu\Cache;

class Pickle
{
	protected $path;
	protected $name;

	public function __construct(string $name, $value = null)
	{
		$this->path = new \Katu\Files\File(debug_backtrace()[0]['file']);
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
		return new \Katu\Files\File(\Katu\App::getTemporaryDir(), 'pickles', [$this->name . '.txt']);
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
}
