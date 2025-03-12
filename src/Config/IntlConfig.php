<?php

namespace Katu\Config;

use Katu\Tools\Intl\Locale;
use Katu\Tools\Intl\LocaleCollection;

abstract class IntlConfig extends \Katu\Config\Config
{
	abstract public function getSupportedLocales(): LocaleCollection;
	abstract public function getDefaultLocale(): Locale;
}
