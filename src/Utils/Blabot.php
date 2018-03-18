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

		$arr = JSON::decodeAsArray(Cache::getUrl('http://api.blabot.net?' . http_build_query($params), 86400));

		return $arr['blabot']['result'];
	}

}
