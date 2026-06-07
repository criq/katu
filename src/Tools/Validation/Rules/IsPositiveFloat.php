<?php

namespace Katu\Tools\Validation\Rules;

use Katu\Errors\Error;
use Katu\Errors\ErrorVersionCollection;
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
			$floatVal = filter_var((string)$output, FILTER_VALIDATE_FLOAT);
			if ($floatVal === false) {
				$message = $this->getMessage() ?: "Hodnota musí být kladné desetinné číslo.";
				$validation->addError((new Error($message, "IS_NOT_POSITIVE_FLOAT", ErrorVersionCollection::createFromArray([
					"cs" => $message ?: "Hodnota musí být kladné desetinné číslo.",
					"sk" => $message ?: "Hodnota musí byť kladné desatinné číslo.",
					"en" => $message ?: "Value must be a positive float.",
				])))->addParam($param));
			} else {
				if ($floatVal <= 0) {
					$message = $this->getMessage() ?: "Hodnota musí být kladné desetinné číslo.";
					$validation->addError((new Error($message, "IS_NOT_POSITIVE_FLOAT", ErrorVersionCollection::createFromArray([
						"cs" => $message ?: "Hodnota musí být kladné desetinné číslo.",
						"sk" => $message ?: "Hodnota musí byť kladné desatinné číslo.",
						"en" => $message ?: "Value must be a positive float.",
					])))->addParam($param));
				} else {
					$validation->addParam($param->setOutput($floatVal));
				}
			}
		}

		return $validation;
	}
}
