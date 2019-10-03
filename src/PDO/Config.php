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

	static function createFromConfig($config) {
		$class = '\\Katu\\PDO\\Config\\' . $config['type'];
		if (!class_exists($class)) {
			throw new \Katu\Exceptions\PDOConfigException("Invalid PDO type.");
		}

		return new $class($config['host'], $config['user'], $config['password'], $config['database'], $config['charset']);
	}

	public function getPDOArray() {
		return [
			'driver'   => static::DRIVER,
			'host'     => $this->host,
			'user'     => $this->user,
			'password' => $this->password,
			'dbname'   => $this->database,
			'charset'  => $this->charset,
		];
	}

	public function getPDODSN() {
		return static::SCHEMA . ':dbname=' . $this->database . ';host=' . $this->host . ';charset=' . $this->charset;
	}

}
