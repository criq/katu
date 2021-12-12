<?php

namespace Katu\Tools\Validation;

use Katu\Errors\ErrorCollection;

class Result extends \ArrayObject
{
	protected $errorCollection;

	public function __construct(?array $params = [])
	{
		foreach ($params as $param) {
			$this->append($param);
		}
	}

	public function offsetSet($key, $value)
	{
		parent::offsetSet($value->getKey(), $value);
	}

	public function getErrorCollection(): ErrorCollection
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
}
