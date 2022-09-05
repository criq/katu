<?php

namespace Katu\Tools\Validation\Rules;

use Katu\Errors\Error;
use Katu\Tools\Validation\Param;
use Katu\Tools\Validation\Rule;
use Katu\Tools\Validation\Validation;

class IsTruthy extends Rule
{
	public function validate(Param $param): Validation
	{
		$validation = new Validation;

		$output = (bool)trim($param);
		if ($output === true) {
			$validation->addParam($param->setOutput($output));
		} else {
			$validation->addError((new Error($this->getMessage()))->addParam($param));
		}

		return $validation;
	}
}
