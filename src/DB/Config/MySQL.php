<?php

namespace Katu\DB\Config;

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
			\Katu\Keychain::get('db', $name, 'host'),
			\Katu\Keychain::get('db', $name, 'user'),
			\Katu\Keychain::get('db', $name, 'password'),
			\Katu\Keychain::get('db', $name, 'database')
		);
	}

}
