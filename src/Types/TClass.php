<?php

namespace Katu\Types;

class TClass
{
	const STORABLE_NAME_DELIMITER = '-';

	public $name;

	public function __construct($name)
	{
		if (is_object($name)) {
			$this->name = get_class($name);
		} else {
			$this->name = $name;
		}
	}

	public function __toString() : string
	{
		return $this->getName();
	}

	public function getName() : string
	{
		return $this->name;
	}

	// public function getPackage() : \Katu\Types\TPackage
	// {
	// 	return new \Katu\Types\TPackage([
	// 		'name' => $this->name,
	// 	]);
	// }

	public static function createFromPackage(\Katu\Types\TPackage $package) : TClass
	{
		return new static($package->getPayload()['name']);
	}

	public function exists() : bool
	{
		try {
			return class_exists($this->getName());
		} catch (\Throwable $e) {
			return false;
		}
	}

	public function getShortName() : string
	{
		return array_slice(explode('\\', $this->name), -1, 1)[0];
	}

	public function getStorableName() : string
	{
		return strtr($this->getName(), '\\', static::STORABLE_NAME_DELIMITER);
	}
}
