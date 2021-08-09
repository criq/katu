<?php

namespace Katu\Types;

use Katu\Interfaces\Packaged;

class TClass implements Packaged
{
	const PORTABLE_NAME_DELIMITER = '-';

	public $name;

	public function __construct($name)
	{
		if (is_object($name)) {
			$this->name = get_class($name);
		} else {
			$this->name = $name;
		}

		$this->name = ltrim($this->name, '\\');
	}

	public function __toString() : string
	{
		return $this->getName();
	}

	public static function createFromPortableName(string $storableName) : TClass
	{
		return new static(strtr($storableName, static::PORTABLE_NAME_DELIMITER, '\\'));
	}

	public function getName() : string
	{
		return $this->name;
	}

	public function getPackage() : \Katu\Types\TPackage
	{
		return new \Katu\Types\TPackage([
			'name' => $this->getName(),
		]);
	}

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

	public function getPortableName() : string
	{
		return strtr($this->getName(), '\\', static::PORTABLE_NAME_DELIMITER);
	}
}
