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
		$this->charset  = $charset;
		$this->database = $database;
		$this->host = $host;
		$this->password = $password;
		$this->type = static::TYPE;
		$this->user = $user;
	}

	public static function createFromConfig($config)
	{
		$class = (string)new \Katu\Tools\Classes\ClassName('Katu', 'PDO', 'Config', $config['type']);
		if (!class_exists($class)) {
			throw new \Katu\Exceptions\PDOConfigException("Invalid PDO type.");
		}

		return new $class($config['host'], $config['user'], $config['password'], $config['database'], $config['charset']);
	}

	public function getPDOArray()
	{
		return [
			'charset' => $this->charset,
			'dbname' => $this->database,
			'driver' => static::DRIVER,
			'host' => $this->host,
			'password' => $this->password,
			'user' => $this->user,
		];
	}

	public function getPDODSN()
	{
		return static::SCHEMA . ':dbname=' . $this->database . ';host=' . $this->host . ';charset=' . $this->charset;
	}
}
