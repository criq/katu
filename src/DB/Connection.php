<?php

namespace Jabli\DB;

class Connection {

	static function connect($config) {
		return new \Dabble\Database($config->host, $config->user, $config->password, $config->database);
	}

	static function getInstance($name) {
		if (!isset($GLOBALS['db'][$name])) {
			$GLOBALS['db'][$name] = self::connect(\Jabli\Config::getDB($name));
		}

		return $GLOBALS['db'][$name];
	}

}
