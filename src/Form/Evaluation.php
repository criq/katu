<?php

namespace Katu\Form;

class Evaluation {

	public $name = NULL;
	public $errors = array();

	public function __construct($name = NULL) {
		$this->name = $name;
	}

	public function addFieldError($type, $fields, $error) {
		$this->errors[] = new FieldError($type, $fields, $error);
	}

	public function hasErrors() {
		return (bool) count($this->errors);
	}

}
