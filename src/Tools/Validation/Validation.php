<?php

namespace Katu\Tools\Validation;

class Validation implements \ArrayAccess
{
	protected $response;
	protected $params;
	protected $errors;

	public function __construct(?ParamCollection $params = null)
	{
		$this->params = $params ?: new ParamCollection;
		$this->errors = new \Katu\Errors\ErrorCollection;
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

	public function getErrors(): \Katu\Errors\ErrorCollection
	{
		if (!$this->errors) {
			$this->errors = new \Katu\Errors\ErrorCollection;
		}

		return $this->errors;
	}

	public function addError(\Katu\Errors\Error $error): Validation
	{
		$this->getErrors()->addError($error);

		return $this;
	}

	public function addErrors(\Katu\Errors\ErrorCollection $errors): Validation
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
