<?php

namespace Jabli\DB\Config;

class MySQL {

	public $host;
	public $user;
	public $password;
	public $database;

	public function __construct($host, $user, $password, $database) {
		$this->host     = $host;
		$this->user     = $user;
		$this->password = $password;
		$this->database = $database;
	}

	static function getFromKeychain($name) {
		return new self(
			\Jabli\Keychain::get('db', $name, 'host'),
			\Jabli\Keychain::get('db', $name, 'user'),
			\Jabli\Keychain::get('db', $name, 'password'),
			\Jabli\Keychain::get('db', $name, 'database')
		);
	}

}
