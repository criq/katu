<?php

namespace Katu\Tools\Validation;

class Validator
{
	protected $rules = [];

	public function addRule(Rule $rule): Validator
	{
		$this->rules[] = $rule;

		return $this;
	}

	public function getRules(): array
	{
		return $this->rules;
	}

	public function validate(Param $param): Validation
	{
		foreach ($this->getRules() as $rule) {
			$validation = $rule->validate($param);
			if ($validation->hasErrors()) {
				return $validation;
			}
		}

		return (new Validation)->addParam($param);
	}
}
