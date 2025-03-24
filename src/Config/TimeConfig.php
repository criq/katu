<?php

namespace Katu\Config;

class TimeConfig extends \Katu\Config\Config
{
	public function getTimezone(): \DateTimeZone
	{
		return new \DateTimeZone("UTC");
	}
}
