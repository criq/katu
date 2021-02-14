<?php

namespace Katu\Tools\Factories;

class ClassFactory extends Factory
{
	protected $className;

	public function __construct(\Katu\Tools\Classes\ClassName $className)
	{
		$this->className = $className;
	}

	public function create()
	{
		$class = (string)$this->className;
		$object = new $class;
		$array = func_get_arg(0);
		foreach ($array as $key => $value) {
			$object->$key = $value;
		}

		return $object;
	}
}
