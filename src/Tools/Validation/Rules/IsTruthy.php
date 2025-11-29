<?php

namespace Katu\Tools\Validation\Rules;

use Katu\Errors\Error;
use Katu\Errors\ErrorVersionCollection;
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
			$message = $this->getMessage() ?: "Hodnota musí být pravdivá.";
			$validation->addError((new Error($message, "IS_NOT_TRUTHY", ErrorVersionCollection::createFromArray([
				"cs" => $message ?: "Hodnota musí být pravdivá.",
				"sk" => $message ?: "Hodnota musí byť pravdivá.",
				"en" => $message ?: "Value must be truthy.",
			])))->addParam($param));
		}

		return $validation;
	}
}
