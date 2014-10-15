<?php

namespace Katu\Pdo;

use \PDO;
use \Katu\Config;

class Connection {

	public $name;
	public $connection;

	static $connections = array();

	public function __construct($name) {
		$this->name = $name;

		try {
			$config = Config::getDB($name);
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			throw new \Katu\Exceptions\MissingPdoConfigException("Missing PDO config for instance " . $name . ".");
		}

		// Try to connect.
		for ($i = 1; $i <= 3; $i++) {
			try {
				$this->connection = new PDO($config->getPdoDSN(), $config->user, $config->password, array(
					PDO::ATTR_PERSISTENT => true,
				));
				break;
			} catch (\ErrorException $e) {
				// Retry.
			}
		}
	}

	static function getInstance($name) {
		if (!isset(static::$connections[$name])) {
			static::$connections[$name] = new self($name);
		}

		return static::$connections[$name];
	}

	public function getLastInsertId() {
		return $this->connection->lastInsertId();
	}



	public function createQuery($sql = null, $params = array()) {
		$query = new Query($this, $sql, $params);

		return $query;
	}

	public function createQueryFromSql(\Sexy\Expression $sql) {
		$query = new Query($this);
		$query->setFromSql($sql);

		return $query;
	}

	public function createClassQuery($class, $sql = null, $params = array()) {
		$query = new Query($this, $sql, $params);
		$query->setClass($class);

		return $query;
	}

	public function createClassQueryFromSql($class, \Sexy\Expression $sql) {
		$query = static::createQueryFromSql($sql);
		$query->setClass($class);

		return $query;
	}

}
