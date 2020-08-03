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
}
