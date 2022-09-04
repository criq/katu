<?php

namespace Katu\Tools\Validation;

class Validator
{
	protected $param;
	protected $rules = [];

	public function __construct(Param $param)
	{
		$this->param = $param;
	}

	public function setParam(Param $param): Validator
	{
		$this->param = $param;

		return $this;
	}

	public function getParam(): Param
	{
		return $this->param;
	}

	public function addRule(Rule $rule): Validator
	{
		$this->rules[] = $rule;

		return $this;
	}

	public function getRules(): array
	{
		return $this->rules;
	}

	public function validate(): Validation
	{
		foreach ($this->getRules() as $rule) {
			$validation = $rule->validate($this->getParam());
			if ($validation->hasErrors()) {
				return $validation;
			}
		}

		return (new Validation)->addParam($this->getParam());
	}
}
