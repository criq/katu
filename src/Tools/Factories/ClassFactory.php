<?php

namespace Katu\Tools\Factories;

class ClassFactory extends Factory
{
	protected $class;

	public function __construct(\ReflectionClass $class)
	{
		$this->class = $class;
	}

	public function getClass() : \ReflectionClass
	{
		return $this->class;
	}

	public function create()
	{
		$class = $this->getClass()->getName();
		$object = new $class;
		$array = func_get_arg(0);
		foreach ($array as $key => $value) {
			$object->$key = $value;
		}

		return $object;
	}
}
