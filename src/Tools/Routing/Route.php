<?php

namespace Katu\Tools\Routing;

use Katu\Types\TClass;
use Katu\Types\TIdentifier;

class Route
{
	protected $controller;
	protected $function;
	protected $methods = ["GET", "POST"];
	protected $name;
	protected $pattern;

	public function __construct(string $pattern, string $controller, string $function, array $methods = null)
	{
		$this->setPattern($pattern);
		$this->setController($controller);
		$this->setFunction($function);
		$this->setMethods($methods ?: $this->getMethods());
	}

	public static function getNameFromRequest(\Slim\Http\Request $request): ?string
	{
		try {
			return $request->getAttribute("route")->getName();
		} catch (\Throwable $e) {
			\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->error($e);

			return null;
		}
	}

	public function setName(string $name): Route
	{
		$this->name = $name;

		return $this;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setPattern(string $pattern): Route
	{
		$this->pattern = $pattern;

		return $this;
	}

	public function getPattern(): string
	{
		return $this->pattern;
	}

	public function setController(string $controller): Route
	{
		$this->controller = $controller;

		return $this;
	}

	public function getController(): string
	{
		return $this->controller;
	}

	public function setFunction(string $function): Route
	{
		$this->function = $function;

		return $this;
	}

	public function getFunction(): string
	{
		return $this->function;
	}

	public function setMethods(array $methods): Route
	{
		$this->methods = $methods;

		return $this;
	}

	public function getMethods(): array
	{
		return $this->methods;
	}

	public function getCallable(): string
	{
		return implode(":", [
			TClass::createFromArray([
				"App",
				"Controllers",
				strtr($this->getController(), "/", "\\"),
			])->getName(),
			$this->getFunction(),
		]);
	}

	public function getArgs(): array
	{
		if (preg_match_all("/(?<arg>\{(?<name>[a-z]+)\})/i", $this->getPattern(), $matches, \PREG_PATTERN_ORDER)) {
			return ($matches["name"]);
		}

		return [];
	}
}
