<?php

namespace Katu\Pdo;

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
			\Katu\Keychain::get('db', $name, 'host'),
			\Katu\Keychain::get('db', $name, 'user'),
			\Katu\Keychain::get('db', $name, 'password'),
			\Katu\Keychain::get('db', $name, 'database'),
			\Katu\Keychain::get('db', $name, 'charset')
		);
	}

	public function getPdoArray() {
		return array(
			'driver'   => static::DRIVER,
			'host'     => $this->host,
			'user'     => $this->user,
			'password' => $this->password,
			'dbname'   => $this->database,
			'charset'  => $this->charset,
		);
	}

	public function getPdoDSN() {
		return static::SCHEMA . ':dbname=' . $this->database . ';host=' . $this->host . ';charset=' . $this->charset;
	}

}
