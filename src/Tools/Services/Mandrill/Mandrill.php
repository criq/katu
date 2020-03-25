<?php

namespace Katu\Utils;

class Mandrill
{
	public static function getDefaultApi()
	{
		try {
			$key = \Katu\Config\Config::get('app', 'email', 'useMandrillKey');
		} catch (\Exception $e) {
			$key = 'live';
		}

		return new \Mandrill(\Katu\Config\Config::get('mandrill', 'api', 'keys', $key));
	}
}
