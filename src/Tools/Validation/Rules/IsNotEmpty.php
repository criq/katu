<?php

namespace Katu\Tools\Validation\Rules;

use Katu\Errors\Error;
use Katu\Tools\Validation\Param;
use Katu\Tools\Validation\Rule;
use Katu\Tools\Validation\Validation;

class IsNotEmpty extends Rule
{
	public function validate(Param $param): Validation
	{
		$validation = new Validation;

		$output = trim($param);
		if (!strlen($output)) {
			$validation->addError((new Error($this->getMessage()))->addParam($param));
		} else {
			$validation->addParam($param->setOutput($output));
		}

		return $validation;
	}
}
