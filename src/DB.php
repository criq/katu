<?php

namespace Jabli\Aids;

class DB {

	static function connect($config) {
		return new \Dabble\Database($config['host'], $config['user'], $config['pswd'], $config['name']);
	}

	static function getNotORM($config) {
		return new \NotORM(new \PDO('mysql:dbname=' . $config['name'] . ';host=' . $config['host'], $config['user'], $config['pswd']));
	}

}
