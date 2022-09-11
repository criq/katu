<?php

namespace Katu\Tools\Validation\Rules;

use Katu\Errors\Error;
use Katu\Tools\Validation\Param;
use Katu\Tools\Validation\Rule;
use Katu\Tools\Validation\Validation;

class IsInteger extends Rule
{
	public function validate(Param $param): Validation
	{
		$validation = new Validation;

		$output = (new \Katu\Types\TString(trim($param)))->getAsFloatIfNumeric();
		if (strlen($output)) {
			if (filter_var($output, FILTER_VALIDATE_INT) === false) {
				$validation->addError((new Error($this->getMessage()))->addParam($param));
			} else {
				$validation->addParam($param->setOutput($output));
			}
		}

		return $validation;
	}
}
