<?php

namespace Katu\Utils;

class Blabot {

	static function getList($params = array()) {
		$params = array_merge(array(
			'scount'     => 100,
			'method'     => 'list',
			'format'     => 'json',
			'language'   => 'cs',
			'dictionary' => 1,
		), $params);

		$arr = JSON::decodeAsArray(Cache::getURL('http://api.blabot.net?' . http_build_query($params), 3600));

		return $arr['blabot']['result'];
	}

}
