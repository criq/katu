<?php

namespace Katu\Config;

class SessionConfig extends \Katu\Config\Config
{
	public function getName(): string
	{
		return "PHPSESSID";
	}
}
