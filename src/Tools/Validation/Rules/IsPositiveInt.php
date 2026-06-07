<?php

namespace Katu\Tools\Validation\Rules;

use Katu\Errors\Error;
use Katu\Errors\ErrorVersionCollection;
use Katu\Tools\Validation\Param;
use Katu\Tools\Validation\Rule;
use Katu\Tools\Validation\Validation;

class IsPositiveInt extends Rule
{
	public function validate(Param $param): Validation
	{
		$validation = new Validation;

		$output = (new \Katu\Types\TString(trim($param)))->getAsFloatIfNumeric();
		if (strlen($output)) {
			$intVal = filter_var((string)$output, FILTER_VALIDATE_INT);
			if ($intVal === false) {
				$message = $this->getMessage() ?: "Hodnota musí být kladné celé číslo.";
				$validation->addError((new Error($message, "IS_NOT_POSITIVE_INT", ErrorVersionCollection::createFromArray([
					"cs" => $message ?: "Hodnota musí být kladné celé číslo.",
					"sk" => $message ?: "Hodnota musí byť kladné celé číslo.",
					"en" => $message ?: "Value must be a positive integer.",
				])))->addParam($param));
			} else {
				if ($intVal <= 0) {
					$message = $this->getMessage() ?: "Hodnota musí být kladné celé číslo.";
					$validation->addError((new Error($message, "IS_NOT_POSITIVE_INT", ErrorVersionCollection::createFromArray([
						"cs" => $message ?: "Hodnota musí být kladné celé číslo.",
						"sk" => $message ?: "Hodnota musí byť kladné celé číslo.",
						"en" => $message ?: "Value must be a positive integer.",
					])))->addParam($param));
				} else {
					$validation->addParam($param->setOutput($intVal));
				}
			}
		}

		return $validation;
	}
}
