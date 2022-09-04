<?php

namespace Katu\Tools\Validation\Rules;

use Katu\Errors\Error;
use Katu\Tools\Validation\Param;
use Katu\Tools\Validation\Rule;
use Katu\Tools\Validation\Validation;

class IsPositiveFloat extends Rule
{
	public function validate(Param $param): Validation
	{
		$validation = new Validation;

		$output = (new \Katu\Types\TString(trim($param)))->getAsFloatIfNumeric();
		if (strlen($output)) {
			if (filter_var($output, FILTER_VALIDATE_FLOAT) === false) {
				$validation->addError((new Error($this->getMessage()))->addParam($param));
			} else {
				if ($output <= 0) {
					$validation->addError((new Error($this->getMessage()))->addParam($param));
				} else {
					$validation->addParam($param->setOutput($output));
				}
			}
		}

		return $validation;
	}
}
