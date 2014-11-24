<?php

namespace Katu\Utils;

class Mandrill {

	static function getDefaultApi() {
		$app = \Katu\App::get();

		try {
			$key = \Katu\Config::get('app', 'email', 'useMandrillKey');
		} catch (\Exception $e) {
			$key = 'live';
		}

		return new \Mandrill(\Katu\Config::get('mandrill', 'api', 'keys', $key));
	}

}
