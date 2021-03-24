<?php

namespace Katu\Types;

class TClass implements \Katu\Interfaces\Packaged
{
	public $class;

	public function __construct(string $class)
	{
		$this->class = $class;
	}

	public function __toString() : string
	{
		return $this->getClass();
	}

	public function getClass() : string
	{
		return $this->class;
	}

	public function getPackage() : \Katu\Types\TPackage
	{
		return new \Katu\Types\TPackage([
			'name' => $this->class,
		]);
	}

	public static function createFromPackage(\Katu\Types\TPackage $package)
	{
		return new static($package->getPayload()['name']);
	}
}
