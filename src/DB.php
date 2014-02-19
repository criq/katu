<?php

namespace Elementary;

class DB {

	static function connect($config) {
		return new \Dabble\Database($config['host'], $config['user'], $config['pswd'], $config['name']);
	}

}
