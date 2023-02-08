<?php

namespace Katu\Types;

use Katu\Tools\Package\Package;
use Katu\Tools\Package\PackagedInterface;

class TClass implements PackagedInterface
{
	const PORTABLE_NAME_DELIMITER = ".";
	const ACCEPTABLE_PORTABLE_NAME_DELIMITER_REGEX = "/[\.\-]/";

	public $name;

	public function __construct($name)
	{
		if (is_object($name)) {
			$this->name = get_class($name);
		} else {
			$this->name = $name;
		}

		$this->name = ltrim(strtr($this->name, "/", "\\"), "\\");
	}

	public function __toString(): string
	{
		return $this->getName();
	}

	public static function createFromPortableName(string $portableName): TClass
	{
		return new static(preg_replace(static::ACCEPTABLE_PORTABLE_NAME_DELIMITER_REGEX, "\\", $portableName));
	}

	public static function createFromArray(array $array): TClass
	{
		return new static(implode("\\", $array));
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getPackage(): Package
	{
		return new Package([
			"name" => $this->getName(),
		]);
	}

	public static function createFromPackage(Package $package): TClass
	{
		return new static($package->getPayload()["name"]);
	}

	public function exists(): bool
	{
		try {
			return class_exists($this->getName());
		} catch (\Throwable $e) {
			return false;
		}
	}

	public function getShortName(): string
	{
		return array_slice(explode("\\", $this->name), -1, 1)[0];
	}

	public function getPortableName(): string
	{
		return strtr($this->getName(), "\\", static::PORTABLE_NAME_DELIMITER);
	}

	public static function getStandardString(string $string): string
	{
		$string = preg_replace("/[^A-Za-z0-9\\\\]/", " ", $string);
		$string = trim($string);
		$string = ucfirst($string);
		$string = preg_replace_callback("/([A-Z])([A-Z]+)/", function () {
			return implode([
				strtoupper(func_get_arg(0)[1]),
				strtolower(func_get_arg(0)[2]),
			]);
		}, $string);
		$string = preg_replace_callback("/\s([a-z])/", function () {
			return strtoupper(func_get_arg(0)[1]);
		}, $string);
		$string = preg_replace("/\s/", "", $string);

		return $string;
	}
}
