<?php

namespace Katu\Tools\Validation\Rules;

use Katu\Errors\Error;
use Katu\Errors\ErrorVersionCollection;
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
			$message = $this->getMessage() ?: "Hodnota nesmí být prázdná.";
			$validation->addError((new Error($message, "IS_EMPTY", ErrorVersionCollection::createFromArray([
				"cs" => $message ?: "Hodnota nesmí být prázdná.",
				"sk" => $message ?: "Hodnota nesmie byť prázdna.",
				"en" => $message ?: "Value must not be empty.",
			])))->addParam($param));
		} else {
			$validation->addParam($param->setOutput($output));
		}

		return $validation;
	}
}
