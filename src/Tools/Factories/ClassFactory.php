<?php

namespace Katu\Tools\Factories;

use Katu\Types\TClass;

class ClassFactory extends Factory
{
	protected $class;
	protected $method;

	public function __construct(TClass $class, ?string $method = null)
	{
		$this->class = $class;
		$this->method = $method;
	}

	public function getClass(): TClass
	{
		return $this->class;
	}

	public function getMethod(): ?string
	{
		return $this->method;
	}

	public function create()
	{
		$className = $this->getClass()->getName();
		$method = $this->getMethod();
		$params = func_get_arg(0);

		if ($method) {
			$object = $className::$method($params);
		} else {
			$object = new $className;
			foreach ($params as $key => $value) {
				$object->$key = $value;
			}
		}

		return $object;
	}
}
