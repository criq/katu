<?php

namespace Katu\Types;

class TLocaleStrings implements \Iterator, \ArrayAccess
{
	public $strings = [];
	protected $iteratorPosition = 0;

	public function add(TPreferredLocale $acceptedLocale)
	{
		$this->strings[] = $acceptedLocale;
	}

	public function getLocaleArray()
	{
		$array = new \Katu\Types\TArray;

		foreach ($this as $localeString) {
			$array[(string) $localeString->locale] = $localeString;
		}

		return $array;
	}

	public function getLanguageArray()
	{
		$array = new \Katu\Types\TArray;

		foreach ($this as $localeString) {
			$array[$localeString->locale->language] = $localeString;
		}

		return $array;
	}

	public function getPreferredString()
	{
		$preferredLocales = \Katu\Tools\Intl\Formatter::getPreferredLocales();

		// Exact match.
		$localeStringArray = $this->getLocaleArray();
		foreach ($preferredLocales as $preferredLocale) {
			if (isset($localeStringArray[(string) $preferredLocale])) {
				return $localeStringArray[(string) $preferredLocale];
			}
		}

		// Language match.
		$languageStringArray = $this->getLanguageArray();
		foreach ($preferredLocales as $preferredLocale) {
			if (isset($languageStringArray[(string) $preferredLocale->locale->language])) {
				return $languageStringArray[(string) $preferredLocale->locale->language];
			}
		}

		return false;
	}

	/****************************************************************************
	 * Iterator.
	 */
	public function rewind()
	{
		$this->iteratorPosition = 0;
	}

	public function current()
	{
		return $this->strings[$this->iteratorPosition];
	}

	public function key()
	{
		return $this->iteratorPosition;
	}

	public function next()
	{
		++$this->iteratorPosition;
	}

	public function valid()
	{
		return isset($this->strings[$this->iteratorPosition]);
	}

	/****************************************************************************
	 * ArrayAccess.
	 */
	public function offsetSet($offset, $value)
	{
		if (is_null($offset)) {
			$this->strings[] = $value;
		} else {
			$this->strings[$offset] = $value;
		}
	}

	public function offsetExists($offset)
	{
		return isset($this->strings[$offset]);
	}

	public function offsetUnset($offset)
	{
		unset($this->strings[$offset]);
	}

	public function offsetGet($offset)
	{
		return isset($this->strings[$offset]) ? $this->strings[$offset] : null;
	}
}
