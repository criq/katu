<?php

namespace Katu\PDO;

use Katu\Types\TClass;

class Config
{
	const DRIVER = "";
	const SCHEMA = "";
	const TYPE = "";

	public $charset;
	public $database;
	public $host;
	public $password;
	public $type;
	public $user;

	public function __construct(string $host, string $user, string $password, string $database, string $charset)
	{
		$this->charset = $charset;
		$this->database = $database;
		$this->host = $host;
		$this->password = $password;
		$this->type = static::TYPE;
		$this->user = $user;
	}

	public static function createFromConfig(array $config): Config
	{
		$class = TClass::createFromArray(["Katu", "PDO", "Config", $config["type"]]);
		if (!$class->exists()) {
			throw new \Katu\Exceptions\PDOConfigException("Invalid PDO type.");
		}

		$className = $class->getName();

		return new $className($config["host"], $config["user"], $config["password"], $config["database"], $config["charset"]);
	}

	public function getPDOArray(): array
	{
		return [
			"charset" => $this->getCharset(),
			"dbname" => $this->getDatabase(),
			"driver" => $this->getDriver(),
			"host" => $this->getHost(),
			"password" => $this->getPassword(),
			"user" => $this->getUser(),
		];
	}

	public function getSchema(): string
	{
		return static::SCHEMA;
	}

	public function getDriver(): string
	{
		return static::DRIVER;
	}

	public function getCharset(): string
	{
		return (string)$this->charset;
	}

	public function getDatabase(): string
	{
		return (string)$this->database;
	}

	public function getHost(): string
	{
		return (string)$this->host;
	}

	public function getUser(): string
	{
		return (string)$this->user;
	}

	public function getPassword(): string
	{
		return (string)$this->password;
	}

	public function getPDODSN(): string
	{
		return "{$this->getSchema()}:dbname={$this->getDatabase()};host={$this->getHost()};charset={$this->getCharset()}";
	}
}
