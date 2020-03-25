<?php

namespace Katu\Tools\Classes;

class ClassName
{
	protected $src;

	public function __construct()
	{
		if (is_object(func_get_arg(0))) {
			$this->src = [get_class(func_get_arg(0))];
		} else {
			$this->src = (new \Katu\Types\TArray(func_get_args()))->flatten()->getArray();
		}
	}

	public function __toString() : string
	{
		return (string)$this->getFullName();
	}

	public function exists() : bool
	{
		return class_exists($this);
	}

	public function getFullName() : string
	{
		return '\\' . implode('\\', array_map(function ($i) {
			return trim($i, '\\');
		}, $this->src));
	}

	public function getName() : string
	{
		return array_values(array_slice(explode('\\', $this->getFullName()), -1))[0];
	}

	public function encode() : string
	{
		return static::encodeClassName($this->getFullName());
	}

	public static function encodeClassName() : string
	{
		return strtr(ltrim(get_called_class(), '\\'), '\\', '-');
	}

	public function getEncodedClassName() : string
	{
		return static::encodeClassName($this);
	}

	public static function decodeClassName($className) : ClassName
	{
		return new static('\\' . ltrim(strtr($className, '-', '\\'), '\\'));
	}
}
