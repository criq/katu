<?php

namespace Katu\Tools\Factories;

use Katu\Types\TClass;

class ClassFactory extends Factory
{
	protected $class;

	public function __construct(TClass $class)
	{
		$this->class = $class;
	}

	public function getClass() : TClass
	{
		return $this->class;
	}

	public function create()
	{
		$className = $this->getClass()->getName();
		$object = new $className;
		$array = func_get_arg(0);
		foreach ($array as $key => $value) {
			$object->$key = $value;
		}

		return $object;
	}
}
