<?php

namespace Katu\Tools\Validation;

class Result implements \ArrayAccess
{
	protected $paramCollection;
	protected $errorCollection;

	public function __construct(?ParamCollection $paramCollection = null)
	{
		$this->paramCollection = $paramCollection ?: new ParamCollection;
		$this->errorCollection = new \Katu\Errors\ErrorCollection;
	}

	public function getParamCollection(): ParamCollection
	{
		return $this->paramCollection;
	}

	public function getErrorCollection(): \Katu\Errors\ErrorCollection
	{
		if (!$this->errorCollection) {
			$this->errorCollection = new ErrorCollection;
		}

		return $this->errorCollection;
	}

	public function addError(\Katu\Errors\Error $e): Result
	{
		$this->getErrorCollection()->addError($e);

		return $this;
	}

	public function hasErrors(): bool
	{
		return $this->getErrorCollection()->hasErrors();
	}

	public function getParam(string $key): ?Param
	{
		return $this[$key] ?? null;
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
