<?php

namespace Katu\Config;

abstract class TimeConfig extends \Katu\Config\Config
{
	abstract public function getTimezone(): \DateTimeZone;
}
