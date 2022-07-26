<?php

namespace Katu\Tools\Routing;

use Katu\Types\TClass;
use Katu\Types\TIdentifier;
use Psr\Http\Message\ServerRequestInterface;

class Route
{
	protected $callback;
	protected $methods = ["GET", "POST"];
	protected $name;
	protected $pattern;

	public function __construct(string $pattern, callable $callback, array $methods = null)
	{
		$this->setPattern($pattern);
		$this->setCallback($callback);
		$this->setMethods($methods ?: $this->getMethods());
	}

	public static function getNameFromRequest(ServerRequestInterface $request): ?string
	{
		try {
			return $request->getAttribute("__route__")->getName();
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

	public function setCallback(callable $callback): Route
	{
		$this->callback = $callback;

		return $this;
	}

	public function getCallback(): callable
	{
		return $this->callback;
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

	public function getArgs(): array
	{
		if (preg_match_all("/(?<arg>\{(?<name>[a-z]+)\})/i", $this->getPattern(), $matches, \PREG_PATTERN_ORDER)) {
			return ($matches["name"]);
		}

		return [];
	}
}
