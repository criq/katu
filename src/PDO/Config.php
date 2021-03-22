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

	public function __construct(string $host, string $user, string $password, string $database, string $charset)
	{
		$this->charset  = $charset;
		$this->database = $database;
		$this->host = $host;
		$this->password = $password;
		$this->type = static::TYPE;
		$this->user = $user;
	}

	public static function createFromConfig(array $config)
	{
		try {
			$class = (new \ReflectionClass("Katu\PDO\Config\\" . $config['type']))->getName();
		} catch (\Throwable $e) {
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
