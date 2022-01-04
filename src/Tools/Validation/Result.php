<?php

namespace Katu\Tools\Validation;

class Result implements \ArrayAccess
{
	protected $output;
	protected $paramCollection;
	protected $errorCollection;

	public function __construct(?ParamCollection $paramCollection = null)
	{
		$this->paramCollection = $paramCollection ?: new ParamCollection;
		$this->errorCollection = new \Katu\Errors\ErrorCollection;
	}

	public function setOutput($value): Result
	{
		$this->output = $value;

		return $this;
	}

	public function getOutput()
	{
		return $this->output;
	}

	public function getParamCollection(): ParamCollection
	{
		return $this->paramCollection;
	}

	public function getParam(string $key): ?Param
	{
		return $this[$key] ?? null;
	}

	public function getErrorCollection(): \Katu\Errors\ErrorCollection
	{
		if (!$this->errorCollection) {
			$this->errorCollection = new ErrorCollection;
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
