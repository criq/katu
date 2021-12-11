<?php

namespace Katu\Tools\Validation;

class ResultCollection extends \ArrayObject
{
	public function getErrorCollection(): \Katu\Errors\ErrorCollection
	{
		$errorCollection = new \Katu\Errors\ErrorCollection;
		foreach ($this as $result) {
			$errorCollection->addErrorCollection($result->getErrorCollection());
		}

		return $errorCollection;
	}

	public function hasErrors(): bool
	{
		return $this->getErrorCollection()->hasErrors();
	}

	public function getResult(): Result
	{
		$merged = new Result;
		foreach ($this as $result) {
			foreach ($result as $param) {
				$merged[] = $param;
			}
			$merged->getErrorCollection()->addErrorCollection($result->getErrorCollection());
		}

		return $merged;
	}
}
