<?php

namespace Katu\Tools\Validation;

use Katu\Errors\ErrorCollection;

class ValidationCollection extends \ArrayObject
{
	public function getErrors(): \Katu\Errors\ErrorCollection
	{
		$errors = new ErrorCollection;
		foreach ($this as $result) {
			$errors->addErrorCollection($result->getErrors());
		}

		return $errors;
	}

	public function hasErrors(): bool
	{
		return $this->getErrors()->hasErrors();
	}

	public function getMerged(): Validation
	{
		$merged = new Validation;
		foreach ($this as $result) {
			$merged->getParams()->addParamCollection($result->getParams());
			$merged->getErrors()->addErrorCollection($result->getErrors());
		}

		return $merged;
	}
}
