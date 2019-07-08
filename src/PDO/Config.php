<?php

namespace Katu\PDO;

class Config {

	public $type;
	public $host;
	public $user;
	public $password;
	public $database;
	public $charset;

	public function __construct($host, $user, $password, $database, $charset) {
		$this->type     = static::TYPE;
		$this->host     = $host;
		$this->user     = $user;
		$this->password = $password;
		$this->database = $database;
		$this->charset  = $charset;
	}

	static function getFromKeychain($name) {
		return new static(
			\Katu\Tools\Security\Keychain::get('db', $name, 'host'),
			\Katu\Tools\Security\Keychain::get('db', $name, 'user'),
			\Katu\Tools\Security\Keychain::get('db', $name, 'password'),
			\Katu\Tools\Security\Keychain::get('db', $name, 'database'),
			\Katu\Tools\Security\Keychain::get('db', $name, 'charset')
		);
	}

	public function getPDOArray() {
		return array(
			'driver'   => static::DRIVER,
			'host'     => $this->host,
			'user'     => $this->user,
			'password' => $this->password,
			'dbname'   => $this->database,
			'charset'  => $this->charset,
		);
	}

	public function getPDODSN() {
		return static::SCHEMA . ':dbname=' . $this->database . ';host=' . $this->host . ';charset=' . $this->charset;
	}

}
