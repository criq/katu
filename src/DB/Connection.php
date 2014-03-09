<?php

namespace Jabli\Utils\DB;

class Connection {

	static function connect($config) {
		return new \Dabble\Database($config['host'], $config['user'], $config['pswd'], $config['name']);
	}

	static function getInstance($config = NULL) {
		if (!isset($GLOBALS['db'])) {
			$GLOBALS['db'] = self::connect(\Jabli\Utils\Config::get('db'));
		}

		return $GLOBALS['db'];
	}

}
