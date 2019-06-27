<?php

namespace Katu\Tools\Strings;

class BaconIpsum {

	static function get($params = []) {
		$params = array_merge([
			'type'             => 'meat-and-filler',
			'sentences'        => 10,
			'start-with-lorem' => 1,
			'format'           => 'json',
		], $params);

		$url = \Katu\Types\TURL::make('https://baconipsum.com/api/', $params);
		$res = \Katu\Cache\URL::get($url, 86400);

		return $res[0];
	}

}
