<?php

namespace Katu\Utils;

class Blabot {

	static function getList($params = []) {
		$params = array_merge([
			'scount'     => 100,
			'method'     => 'list',
			'format'     => 'json',
			'language'   => 'cs',
			'dictionary' => 1,
		], $params);

		$url = \Katu\Types\TUrl::make('http://api.blabot.net', $params);
		$res = JSON::decodeAsArray(\Katu\Cache\Url::get($url, 86400));

		return $res['blabot']['result'];
	}

}
