<?php

namespace Katu\Tools\Intl;

class Locale
{
	protected $code;
	protected $preference;

	public function __construct(string $code, $preference = null)
	{
		$this->setCode($code);
		$this->setPreference($preference);
	}

	public function __toString(): string
	{
		return $this->getCode();
	}

	public static function createFromHeaderString(string $string): Locale
	{
		list($codeString, $preferenceString) = array_pad(explode(";", $string), 2, null);

		return new static(new static($codeString), $preferenceString);
	}

	public function setCode(string $code): Locale
	{
		$this->code = \Locale::canonicalize($code);

		return $this;
	}

	public function getCode(): string
	{
		return $this->code;
	}

	public function getLanguageCode(): string
	{
		return \Locale::getPrimaryLanguage($this->getCode());
	}

	public function setPreference($preference = null): Locale
	{
		if (is_float($preference) || is_int($preference)) {
			$this->preference = (float)$preference;
		} elseif (preg_match("/^q=(?<preference>.+)$/", $preference, $match)) {
			$this->preference = (float)$match["preference"];
		} else {
			$this->preference = null;
		}

		return $this;
	}

	public function getPreference(): ?float
	{
		return $this->preference;
	}

	public static function getSupportedLocales(): LocaleCollection
	{
		$res = new LocaleCollection;

		try {
			foreach (\Katu\Config\Config::get("intl", "locales", "supported") as $code) {
				$res[] = new static($code);
			}
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			// Nevermind.
		}

		return $res;
	}

	public function getIsSupported(): bool
	{
		return in_array($this->getCode(), static::getSupportedLocales()->getArrayCopy()) || in_array($this->getLanguageCode(), static::getSupportedLocales()->getArrayCopy());
	}
}
