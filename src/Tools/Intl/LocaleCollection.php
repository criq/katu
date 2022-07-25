<?php

namespace Katu\Tools\Intl;

use Psr\Http\Message\ServerRequestInterface;

class LocaleCollection extends \ArrayObject
{
	public static function createFromRequest(ServerRequestInterface $request): LocaleCollection
	{
		$res = new static;

		foreach (explode(",", $request->getHeaderLine("Accept-Language")) as $string) {
			$res[] = Locale::createFromHeaderString($string);
		}

		return $res;
	}

	public function addSupported(): LocaleCollection
	{
		foreach (Locale::getSupportedLocales() as $locale) {
			$this[] = $locale;
		}

		return $this;
	}

	public function filterSupported(): LocaleCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Locale $locale) {
			return $locale->getIsSupported();
		})));
	}

	public function getResolved(): LocaleCollection
	{
		return $this
			->addSupported()
			->filterSupported()
			->sortByPreference()
			;
	}

	public function sortByPreference(): LocaleCollection
	{
		$array = $this->getArrayCopy();
		usort($array, function (Locale $a, Locale $b) {
			return (float)$a->getPreference() > (float)$b->getPreference() ? -1 : 1;
		});

		return new static(array_values($array));
	}
}
