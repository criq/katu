<?php

namespace Katu\Tools\Routing;

class Route
{
	protected $controller;
	protected $function;
	protected $methods = ['GET', 'POST'];
	protected $name;
	protected $pattern;

	public function __construct($pattern, $controller, $function, $methods = null)
	{
		$this->pattern    = $pattern;
		$this->controller = $controller;
		$this->function   = $function;
		$this->methods    = $methods ?: $this->methods;
	}

	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	public function getMethods()
	{
		return $this->methods;
	}

	public function getPattern()
	{
		return $this->pattern;
	}

	public function getArgs()
	{
		if (preg_match_all('/(?<arg>\{(?<name>[a-z]+)\})/i', $this->getPattern(), $matches, \PREG_PATTERN_ORDER)) {
			return ($matches['name']);
		}

		return [];
	}

	public function getCallable()
	{
		return implode(':', [
			"\\App\\Controllers\\" . strtr($this->controller, '/', '\\'),
			$this->function,
		]);
	}

	public static function getName(\Slim\Http\Request $request)
	{
		return $request->getAttributes('route')['route']->getName();
	}
}
