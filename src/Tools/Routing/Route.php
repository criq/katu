<?php

namespace Katu\Tools\Routing;

class Route {

	public $name;
	public $pattern;
	public $controller;
	public $method;
	public $httpMethods;

	public function __construct($pattern, $controller, $method, $httpMethods = null) {
		$this->pattern     = $pattern;
		$this->controller  = $controller;
		$this->method      = $method;
		$this->httpMethods = $httpMethods;
	}

	public function getPattern() {
		return rtrim($this->pattern, '/') . '/';
	}

	public function getCallable() {
		return [
			"\\App\\Controllers\\" . strtr($this->controller, '/', '\\'),
			$this->method,
		];
	}

	public function isCallable() {
		return is_callable($this->getCallable());
	}

	public function setName($name) {
		$this->name = $name;

		return $this;
	}

	static function getCurrentName() {
		return \Katu\App::get()->router()->getCurrentRoute()->getName();
	}

}
