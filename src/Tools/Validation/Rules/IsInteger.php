<?php

namespace Katu\Tools\Validation\Rules;

use Katu\Errors\Error;
use Katu\Errors\ErrorVersionCollection;
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
				$message = $this->getMessage() ?: "Hodnota musí být celé číslo.";
				$validation->addError((new Error($message, "IS_NOT_INTEGER", ErrorVersionCollection::createFromArray([
					"cs" => $message ?: "Hodnota musí být celé číslo.",
					"sk" => $message ?: "Hodnota musí byť celé číslo.",
					"en" => $message ?: "Value must be an integer.",
				])))->addParam($param));
			} else {
				$validation->addParam($param->setOutput($output));
			}
		}

		return $validation;
	}
}
