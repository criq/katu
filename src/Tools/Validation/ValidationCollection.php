<?php

namespace Katu\Tools\Validation;

use Katu\Errors\ErrorCollection;

class ValidationCollection extends \ArrayObject
{
	public function getErrors(): \Katu\Errors\ErrorCollection
	{
		$errors = new ErrorCollection;
		foreach ($this as $validation) {
			$errors->addErrorCollection($validation->getErrors());
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
		foreach ($this as $validation) {
			$merged->getParams()->addParamCollection($validation->getParams());
			$merged->getErrors()->addErrorCollection($validation->getErrors());
		}

		return $merged;
	}
}
