<?php

namespace Katu\Tools\Validation\Rules;

use Katu\Errors\Error;
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
			$validation->addError((new Error($this->getMessage()))->addParam($param));
		} else {
			$validation->addParam($param->setOutput($output));
		}

		return $validation;
	}
}


