<?php

namespace Katu\Form;

use \Katu\App;
use \Katu\Session;
use \Katu\Tools\Session\Flash;

class Evaluation {

	public $name = null;
	public $params = array();
	public $errors = array();

	public function __construct($name = null) {
		$app = App::get();

		// Set form name.
		$this->name = $name;

		// Set form params.
		$this->params = $app->request->params();
	}

	public function getParam($param) {
		return isset($this->params[$param]) ? $this->params[$param] : null;
	}

	public function addError($error) {
		if ($error instanceof \Exception) {
			if ($error->getMessage()) {
				$this->errors[] = new Error($error->getMessage());

				return true;
			}

			return false;
		}

		if (trim($error)) {
			$this->errors[] = new Error($error);

			return true;
		}

		return false;
	}

	public function addFieldError($type, $fields, $error) {
		if (trim($error)) {
			$this->errors[] = new FieldError($type, $fields, $error);

			return true;
		}

		return false;
	}

	public function hasErrors() {
		return (bool) count($this->errors);
	}

	public function setFlash() {
		foreach ($this->errors as $error) {
			// Set errors.
			Flash::add('forms.' . $this->name . '.errors', $error->error);

			// Set field errors.
			if ($error instanceof FieldError) {
				foreach ($error->fields as $field) {
					Flash::add('forms.' . $this->name . '.fieldsInError', $field);
				}
			}

		}
	}

	public function setSession() {
		// Set values.
		foreach ($this->params as $key => $value) {
			Session::set('forms.' . $this->name . '.values.' . $key, $value);
		}
	}

}
