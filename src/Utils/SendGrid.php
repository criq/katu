<?php

namespace Katu\Utils;

class SendGrid {

	static function getDefaultApi() {
		$app = \Katu\App::get();

		try {
			$key = \Katu\Config::get('app', 'email', 'useSendGridKey');
		} catch (\Exception $e) {
			$key = 'live';
		}

		return new \SendGrid(\Katu\Config::get('sendgrid', 'api', 'keys', $key));
	}

}
