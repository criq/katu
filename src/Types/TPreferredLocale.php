<?php

namespace Katu\Types;

class TPreferredLocale
{
	public $locale = null;
	public $preference = 1;

	public function __construct(TLocale $locale, $preference = null)
	{
		$this->locale = $locale;
		$this->preference = (float) $preference;
	}

	public function __toString()
	{
		return (string) $this->locale;
	}

	public static function getFromRequestHeader($src)
	{
		$language = null;
		$country = null;
		$preference = 1;

		foreach (explode(';', $src) as $key => $part) {
			if ($key === 0) {
				$expl = explode('-', $part);
				if (isset($expl[0])) {
					$language = $expl[0];
				}
				if (isset($expl[1])) {
					$country = $expl[1];
				}
			} else {
				$expl = explode('=', $part);
				if (isset($expl[0], $expl[1]) && $expl[0] == 'q') {
					$preference = (float) $expl[1];
				}
			}
		}

		return new static(new TLocale($language, $country), $preference);
	}
}
