<?php

namespace Katu\Utils;

class BaconIpsum {

	static function get($params = []) {
		$params = array_merge([
			'type'             => 'meat-and-filler',
			'sentences'        => 10,
			'start-with-lorem' => 1,
			'format'           => 'json',
		], $params);

		$url = \Katu\Types\TUrl::make('https://baconipsum.com/api/', $params);
		$res = \Katu\Cache\Url::get($url, 86400);

		return $res[0];
	}

}
