<?php

namespace Katu;

class Route
{
	public $name;
	public $pattern;
	public $controller;
	public $method;
	public $conditions;

	public function __construct($pattern, $controller, $method = 'index', $conditions = [])
	{
		$this->pattern    = $pattern;
		$this->controller = $controller;
		$this->method     = $method;
		$this->conditions = $conditions;
	}

	public static function create($pattern, $controller, $method = 'index', $conditions = [])
	{
		return new self($pattern, $controller, $method, $conditions);
	}

	public function getPattern()
	{
		return rtrim($this->pattern, '/') . '/';
	}

	public function getCallable()
	{
		return [
			"\App\Controllers\\" . strtr($this->controller, '/', '\\'),
			$this->method,
		];
	}

	public function isCallable()
	{
		return is_callable($this->getCallable());
	}

	public function setConditions($conditions = [])
	{
		$this->conditions = $conditions;

		return $this;
	}

	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	public static function getCurrentName()
	{
		return \Katu\App::get()->router()->getCurrentRoute()->getName();
	}
}
