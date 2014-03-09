<?php

namespace Jabli\DB;

class Connection {

	static function connect($config) {
		return new \Dabble\Database($config['host'], $config['user'], $config['pswd'], $config['name']);
	}

	static function getInstance($config = NULL) {
		if (!isset($GLOBALS['db'])) {
			$GLOBALS['db'] = self::connect(\Jabli\Config::get('db'));
		}

		return $GLOBALS['db'];
	}

}
