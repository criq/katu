<?php

namespace Katu\Tools\Validation;

class Result implements \ArrayAccess
{
	protected $response;
	protected $params;
	protected $errors;

	public function __construct(?ParamCollection $params = null)
	{
		$this->params = $params ?: new ParamCollection;
		$this->errors = new \Katu\Errors\ErrorCollection;
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
		return $this->params;
	}

	public function getParam(string $key): ?Param
	{
		return $this[$key] ?? null;
	}

	public function addParamCollection(ParamCollection $params): Result
	{
		$this->getParamCollection()->addParamCollection($params);

		return $this;
	}

	public function getErrors(): \Katu\Errors\ErrorCollection
	{
		if (!$this->errors) {
			$this->errors = new \Katu\Errors\ErrorCollection;
		}

		return $this->errors;
	}

	public function addError(\Katu\Errors\Error $error): Result
	{
		$this->getErrors()->addError($error);

		return $this;
	}

	public function addErrorCollection(\Katu\Errors\ErrorCollection $errors): Result
	{
		$this->getErrors()->addErrorCollection($errors);

		return $this;
	}

	public function addResult(Result $result): Result
	{
		$this->addParamCollection($result->getParamCollection());
		$this->addErrorCollection($result->getErrors());

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
