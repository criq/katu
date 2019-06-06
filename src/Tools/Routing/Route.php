<?php

namespace Katu\Tools\Routing;

class Route {

	protected $name;
	protected $pattern;
	protected $controller;
	protected $function;
	protected $methods = ['GET', 'POST'];

	public function __construct($pattern, $controller, $function, $methods = null) {
		$this->pattern    = $pattern;
		$this->controller = $controller;
		$this->function   = $function;
		$this->methods    = $methods ?: $this->methods;
	}

	public function setName($name) {
		$this->name = $name;

		return $this;
	}

	public function getMethods() {
		return $this->methods;
	}

	public function getPattern() {
		return $this->pattern;
	}

	public function getCallable() {
		return implode(':', [
			"\\App\\Controllers\\" . strtr($this->controller, '/', '\\'),
			$this->function,
		]);
	}

	static function getCurrentName() {
		return \Katu\App::get()->router()->getCurrentRoute()->getName();
	}

}
