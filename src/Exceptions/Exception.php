<?php

namespace Katu\Exceptions;

class Exception extends \Exception
{
	private $errorNames = [];
	private $abbr;
	private $translations;

	public function __construct($message = null, $code = 0, $previous = null)
	{
		parent::__construct($message, $code, $previous);

		$this->translations = new \Katu\Types\TLocaleStrings;
	}

	public function __toString()
	{
		return (string) $this->getTranslatedMessage();
	}

	public function setAbbr($abbr)
	{
		$this->abbr = trim($abbr);

		return $this;
	}

	public function getAbbr()
	{
		return $this->abbr;
	}

	public function addErrorName($errorName)
	{
		$this->errorNames[] = static::getErrorName($errorName);

		$this->maintainErrorNames();

		return $this;
	}

	public static function getErrorName($errorName)
	{
		return implode('.', array_filter((array) $errorName));
	}

	public function getErrorNameIndex($errorName)
	{
		return array_search(static::getErrorName($errorName), $this->errorNames);
	}

	public function replaceErrorName($errorName, $replacement)
	{
		$index = $this->getErrorNameIndex($errorName);
		if ($index !== false && isset($this->errorNames[$index])) {
			$this->errorNames[$index] = static::getErrorName($replacement);
		}

		$this->maintainErrorNames();

		return $this;
	}

	private function maintainErrorNames()
	{
		$this->errorNames = array_values(array_unique(array_filter($this->errorNames)));

		return $this;
	}

	public function getErrorNames()
	{
		return $this->errorNames;
	}

	public function addTranslation($locale, $message)
	{
		if (is_string($locale)) {
			$locale = new \Katu\Types\TLocale($locale);
		}

		$this->translations[] = new \Katu\Types\TLocaleString($locale, $message);

		return $this;
	}

	public function getTranslatedMessage()
	{
		$translation = $this->translations->getPreferredString();
		if ($translation) {
			return $translation;
		}

		return $this->getMessage();
	}
}
