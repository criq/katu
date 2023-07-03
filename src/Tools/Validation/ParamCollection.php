<?php

namespace Katu\Tools\Validation;

use Katu\Tools\Options\OptionCollection;
use Katu\Tools\Package\Package;
use Katu\Tools\Package\PackagedInterface;
use Katu\Tools\Rest\RestResponse;
use Katu\Tools\Rest\RestResponseInterface;
use Katu\Tools\Validation\Params\GeneratedParam;
use Katu\Tools\Validation\Params\RequestParam;
use Katu\Types\TClass;
use Psr\Http\Message\ServerRequestInterface;

class ParamCollection extends \ArrayObject implements PackagedInterface, RestResponseInterface
{
	public function __construct(?array $params = [])
	{
		foreach ($params as $param) {
			$this->append($param);
		}
	}

	public static function createFromRequest(ServerRequestInterface $request): ParamCollection
	{
		return static::createFromArray(array_merge((array)$request->getQueryParams(), (array)$request->getParsedBody()));
	}

	public static function createFromArray(array $array): ParamCollection
	{
		$res = new static;
		foreach ($array as $key => $value) {
			$res[] = new RequestParam($key, $value);
		}

		return $res;
	}

	/****************************************************************************
	 * Offset.
	 */
	public function offsetSet($key, $value)
	{
		parent::offsetSet($key ?: $value->getKey(), $value);
	}

	/****************************************************************************
	 * PackagedInterface.
	 */
	public function getPackage(): Package
	{
		return new Package([
			"class" => (new TClass($this))->getPackage(),
			"params" => new Package(array_map(function (Param $param) {
				return $param->getPackage();
			}, $this->getArrayCopy())),
		]);
	}

	public static function createFromPackage(Package $package)
	{
	}

	/****************************************************************************
	 * RestResponseInterface.
	 */
	public function getRestResponse(?ServerRequestInterface $request = null, ?OptionCollection $options = null): RestResponse
	{
		return new RestResponse(array_map(function (Param $param) use ($request, $options) {
			return $param->getRestResponse($request, $options);
		}, $this->getArrayCopy()));
	}

	/****************************************************************************
	 * Filter.
	 */
	public function getByKey(string $key): Param
	{
		$param = ($this[$key] ?? null);
		if ($param) {
			return $param;
		}

		$param = $this->getByAlias($key);
		if ($param) {
			return $param;
		}

		$param = new GeneratedParam($key);
		$this[] = $param;

		return $param;
	}

	public function getByAlias(string $alias): ?Param
	{
		return $this->filterByAlias($alias)->getFirst();
	}

	public function filterByAlias(string $alias): ParamCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Param $param) use ($alias) {
			return $param->hasAlias($alias);
		})));
	}

	public function get(string $key): Param
	{
		return $this->getByKey($key);
	}

	public function getWithKeys(array $keys): ParamCollection
	{
		$res = clone $this;

		foreach ($keys as $key) {
			if (!$this->getByKey($key)) {
				$res[] = new Param($key);
			}
		}

		return $res;
	}

	public function filterWithoutKeys(array $keys): ParamCollection
	{
		$res = new static;
		foreach ($this as $param) {
			if (!in_array($param->getKey(), $keys)) {
				$res[] = $param;
			}
		}

		return $res;
	}

	public function addParams(ParamCollection $params): ParamCollection
	{
		foreach ($params as $param) {
			$this[] = $param;
		}

		return $this;
	}

	public function forwardInputs(): ParamCollection
	{
		array_map(function (Param $param) {
			return $param->forwardInput();
		}, $this->getArrayCopy());

		return $this;
	}

	public function getFirst(): ?Param
	{
		return array_values($this->getArrayCopy())[0] ?? null;
	}
}
