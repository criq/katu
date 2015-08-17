<?php

namespace Katu\Types;

class TPreferredLocaleCollection implements \Iterator, \ArrayAccess {

	public $acceptedLocales = [];

	private $iteratorPosition;

	public function add(TPreferredLocale $acceptedLocale) {
		$this->acceptedLocales[] = $acceptedLocale;
	}

	public function getPreferredLanguages() {
		return array_map(function($i) {
			return $i->locale->language;
		}, $this->acceptedLocales);
	}

	public function getLanguageFromConfig() {
		try {
			$supported = \Katu\Config::get('locales', 'supported');
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			$supported = [];
		}

		try {
			$fallback = \Katu\Config::get('locales', 'fallback');
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			$fallback = [];
		}

		$preferredLanguages = $this->getPreferredLanguages();

		foreach ($preferredLanguages as $preferredLanguage) {

			if (in_array($preferredLanguage, $supported)) {
				return $preferredLanguage;
			}

			if (isset($fallback[$preferredLanguage])) {
				return $fallback[$preferredLanguage];
			}

		}

		if (isset($preferredLanguages[0])) {
			return $preferredLanguages[0];
		}

		return false;
	}

	/* Iterator **************************************************************/

	public function rewind() {
		$this->iteratorPosition = 0;
	}

	public function current() {
		return $this->acceptedLocales[$this->iteratorPosition];
	}

	public function key() {
		return $this->iteratorPosition;
	}

	public function next() {
		++$this->iteratorPosition;
	}

	public function valid() {
		return isset($this->acceptedLocales[$this->iteratorPosition]);
	}

	/* ArrayAccess ***********************************************************/

	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->acceptedLocales[] = $value;
		} else {
			$this->acceptedLocales[$offset] = $value;
		}
	}

	public function offsetExists($offset) {
		return isset($this->acceptedLocales[$offset]);
	}

	public function offsetUnset($offset) {
		unset($this->acceptedLocales[$offset]);
	}

	public function offsetGet($offset) {
		return isset($this->acceptedLocales[$offset]) ? $this->acceptedLocales[$offset] : null;
	}

}
