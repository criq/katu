<?php

namespace Katu\Form;

use \Katu\Flash;

class Evaluation {

	public $name = NULL;
	public $errors = array();

	public function __construct($name = NULL) {
		$this->name = $name;
	}

	public function addError($error) {
		if ($error instanceof \Exception) {
			if ($error->getMessage()) {
				$this->errors[] = new Error($error->getMessage());

				return TRUE;
			}

			return FALSE;
		}

		if (trim($error)) {
			$this->errors[] = new Error($error);

			return TRUE;
		}

		return FALSE;
	}

	public function addFieldError($type, $fields, $error) {
		if (trim($error)) {
			$this->errors[] = new FieldError($type, $fields, $error);

			return TRUE;
		}

		return FALSE;
	}

	public function hasErrors() {
		return (bool) count($this->errors);
	}

	public function setFlash() {
		foreach ($this->errors as $error) {

			// Set errors.
			#Flash::add('formErrors.' . $this->name . )
			#var_dump($error);

		}
	}

}
