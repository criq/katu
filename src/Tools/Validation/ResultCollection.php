<?php

namespace Katu\Tools\Validation;

class ResultCollection extends \ArrayObject
{
	public function getErrors(): \Katu\Errors\ErrorCollection
	{
		$errors = new \Katu\Errors\ErrorCollection;
		foreach ($this as $result) {
			$errors->addErrorCollection($result->getErrors());
		}

		return $errors;
	}

	public function hasErrors(): bool
	{
		return $this->getErrors()->hasErrors();
	}

	public function getResult(): Result
	{
		$merged = new Result;
		foreach ($this as $result) {
			$merged->getParamCollection()->addParamCollection($result->getParamCollection());
			$merged->getErrors()->addErrorCollection($result->getErrors());
		}

		return $merged;
	}
}
