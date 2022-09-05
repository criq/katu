<?php

namespace Katu\Tools\Validation;

interface ValidatableInterface
{
	public function validate(Param $param): Validation;
}
