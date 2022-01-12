<?php

namespace Katu\Tools\Validation;

class Result implements \ArrayAccess
{
	protected $response;
	protected $paramCollection;
	protected $errorCollection;

	public function __construct(?ParamCollection $paramCollection = null)
	{
		$this->paramCollection = $paramCollection ?: new ParamCollection;
		$this->errorCollection = new \Katu\Errors\ErrorCollection;
	}

	public function setResponse($value): Result
	{
		$this->response = $value;

		return $this;
	}

	public function getResponse()
	{
		return $this->response;
	}

	public function addParam(Param $param): Result
	{
		$this[] = $param;

		return $this;
	}

	public function getParamCollection(): ParamCollection
	{
		return $this->paramCollection;
	}

	public function getParam(string $key): ?Param
	{
		return $this[$key] ?? null;
	}

	public function addParamCollection(ParamCollection $paramCollection): Result
	{
		$this->getParamCollection()->addParamCollection($paramCollection);

		return $this;
	}

	public function getErrorCollection(): \Katu\Errors\ErrorCollection
	{
		if (!$this->errorCollection) {
			$this->errorCollection = new \Katu\Errors\ErrorCollection;
		}

		return $this->errorCollection;
	}

	public function addError(\Katu\Errors\Error $error): Result
	{
		$this->getErrorCollection()->addError($error);

		return $this;
	}

	public function addErrorCollection(\Katu\Errors\ErrorCollection $errorCollection): Result
	{
		$this->getErrorCollection()->addErrorCollection($errorCollection);

		return $this;
	}

	public function addResult(Result $result): Result
	{
		$this->addParamCollection($result->getParamCollection());
		$this->addErrorCollection($result->getErrorCollection());

		return $this;
	}

	public function hasErrors(): bool
	{
		return $this->getErrorCollection()->hasErrors();
	}

	/****************************************************************************
	 * ArrayAccess.
	 */
	public function offsetExists($offset)
	{
		return isset($this->getParamCollection()[$offset]);
	}

	public function offsetGet($offset)
	{
		return $this->getParamCollection()[$offset];
	}

	public function offsetSet($offset, $value)
	{
		$this->getParamCollection()[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		unset($this->getParamCollection()[$offset]);
	}
}
