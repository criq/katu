<?php

namespace Katu\Tools\Validation\Rules;

use Katu\Errors\Error;
use Katu\Errors\ErrorVersionCollection;
use Katu\Tools\Validation\Param;
use Katu\Tools\Validation\Rule;
use Katu\Tools\Validation\Validation;

class IsOneOf extends Rule
{
	protected $options;

	public function __construct(?string $message = null, array $options = [])
	{
		parent::__construct(...func_get_args());
		$this->options = $options;
	}

	public function validate(Param $param): Validation
	{
		$validation = new Validation;

		$output = trim($param);
		if (!in_array($output, $this->options)) {
			$message = $this->getMessage() ?: "Hodnota není v seznamu povolených hodnot.";
			$validation->addError((new Error($message, "IS_NOT_ONE_OF", ErrorVersionCollection::createFromArray([
				"cs" => $message ?: "Hodnota není v seznamu povolených hodnot.",
				"sk" => $message ?: "Hodnota nie je v zozname povolených hodnôt.",
				"en" => $message ?: "Value is not in the list of allowed values.",
			])))->addParam($param));
		} else {
			$validation->addParam($param->setOutput($output));
		}

		return $validation;
	}
}
