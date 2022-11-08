<?php

namespace Katu\Tools\Validation;

use Katu\Errors\ErrorCollection;
use Katu\Tools\Package\Package;
use Katu\Tools\Package\PackagedInterface;
use Katu\Types\TClass;

class Validation implements \ArrayAccess, PackagedInterface
{
	protected $errors;
	protected $params;
	protected $response;

	public function __construct(?ParamCollection $params = null)
	{
		$this->params = $params ?: new ParamCollection;
		$this->errors = new ErrorCollection;
	}

	public function getPackage(): Package
	{
		return new Package([
			"class" => (new TClass($this))->getPackage(),
			"errors" => $this->getErrors()->getPackage(),
			"params" => $this->getParams()->getPackage(),
		]);
	}

	public static function createFromPackage(Package $package)
	{
	}

	public function setResponse($value): Validation
	{
		$this->response = $value;

		return $this;
	}

	public function getResponse()
	{
		return $this->response;
	}

	public function addParam(Param $param): Validation
	{
		$this[] = $param;

		return $this;
	}

	public function getParams(): ParamCollection
	{
		return $this->params;
	}

	public function getParam(string $key): ?Param
	{
		return $this[$key] ?? null;
	}

	public function addParamCollection(ParamCollection $params): Validation
	{
		$this->getParams()->addParamCollection($params);

		return $this;
	}

	public function getErrors(): ErrorCollection
	{
		if (!$this->errors) {
			$this->errors = new ErrorCollection;
		}

		return $this->errors;
	}

	public function addError(\Katu\Errors\Error $error): Validation
	{
		$this->getErrors()->addError($error);

		return $this;
	}

	public function addErrors(ErrorCollection $errors): Validation
	{
		$this->getErrors()->addErrors($errors);

		return $this;
	}

	public function addResult(Validation $result): Validation
	{
		$this->addParamCollection($result->getParams());
		$this->addErrors($result->getErrors());

		return $this;
	}

	public function hasErrors(): bool
	{
		return $this->getErrors()->hasErrors();
	}

	/****************************************************************************
	 * ArrayAccess.
	 */
	public function offsetExists($offset)
	{
		return isset($this->getParams()[$offset]);
	}

	public function offsetGet($offset)
	{
		return $this->getParams()[$offset];
	}

	public function offsetSet($offset, $value)
	{
		$this->getParams()[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		unset($this->getParams()[$offset]);
	}
}
