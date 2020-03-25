<?php

namespace Katu\PDO;

class Config
{
	const DRIVER = null;
	const SCHEMA = null;
	const TYPE = null;

	public $charset;
	public $database;
	public $host;
	public $password;
	public $type;
	public $user;

	public function __construct($host, $user, $password, $database, $charset)
	{
		$this->type     = static::TYPE;
		$this->host     = $host;
		$this->user     = $user;
		$this->password = $password;
		$this->database = $database;
		$this->charset  = $charset;
	}

	public static function createFromConfig($config)
	{
		$class = '\\Katu\\PDO\\Config\\' . $config['type'];
		if (!class_exists($class)) {
			throw new \Katu\Exceptions\PDOConfigException("Invalid PDO type.");
		}

		return new $class($config['host'], $config['user'], $config['password'], $config['database'], $config['charset']);
	}

	public function getPDOArray()
	{
		return [
			'driver'   => static::DRIVER,
			'host'     => $this->host,
			'user'     => $this->user,
			'password' => $this->password,
			'dbname'   => $this->database,
			'charset'  => $this->charset,
		];
	}

	public function getPDODSN()
	{
		return static::SCHEMA . ':dbname=' . $this->database . ';host=' . $this->host . ';charset=' . $this->charset;
	}
}
