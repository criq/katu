<?php

namespace Katu\Exceptions;

class Exception extends \Exception {

	private $errorNames = [];
	private $translations = null;

	public function __construct($message = null, $code = 0, $previous = null) {
		parent::__construct($message, $code, $previous);

		$this->translations = new \Katu\Types\TLocaleStrings;
	}

	public function addErrorName($errorName) {
		$this->errorNames[] = $errorName;

		$this->maintainErrorNames();

		return $this;
	}

	public function getErrorNameIndex($errorName) {
		return array_search($errorName, $this->errorNames);
	}

	public function removeErrorName($errorName) {
		$index = $this->getErrorNameIndex($errorName);
		if ($index !== false && isset($this->errorNames[$index])) {
			unset($this->errorNames[$index]);
		}

		$this->maintainErrorNames();

		return $this;
	}

	public function replaceErrorName($errorName, $replacement) {
		$index = $this->getErrorNameIndex($errorName);
		if ($index !== false && isset($this->errorNames[$index])) {
			$this->errorName[$index] = $replacement;
		}

		$this->maintainErrorNames();

		return $this;
	}

	private function maintainErrorNames() {
		$this->errorNames = array_values(array_unique(array_filter($this->errorNames)));

		return $this;
	}

	public function getErrorNames() {
		return $this->errorNames;
	}

	public function getErrors() {
		$errors = [];

		if ($this->errorNames) {
			foreach ($this->errorNames as $errorName) {
				$errors[$errorName] = $this->getTranslatedMessage();
			}
		} else {
			$errors[] = $this->getTranslatedMessage();
		}

		return $errors;
	}

	public function addTranslation($locale, $message) {
		if (is_string($locale)) {
			$locale = new \Katu\Types\TLocale($locale);
		}

		$this->translations[] = new \Katu\Types\TLocaleString($locale, $message);

		return $this;
	}

	public function getTranslatedMessage() {
		$translation = $this->translations->getPreferredString();
		if ($translation) {
			return $translation;
		}

		return $this->getMessage();
	}

}
