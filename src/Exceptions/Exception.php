<?php

namespace Katu\Exceptions;

class Exception extends \Exception {

	public $context = [];
	private $translations = null;

	public function __construct($message = null, $code = 0, $context = [], $previous = null) {
		parent::__construct($message, $code, $previous);

		$this->context = $context;
		$this->translations = new \Katu\Types\TLocaleStrings;
	}

	public function translate($locale, $message) {
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
