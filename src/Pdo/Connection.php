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

		$this->connection = new PDO($config->getPdoDSN(), $config->user, $config->password, array(
			PDO::ATTR_PERSISTENT => TRUE,
		));
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



	public function createQuery($sql = NULL, $params = array()) {
		return new Query($this, $sql, $params);
	}

	public function createQueryFromSql(Expression $sql) {
		return new Query($this, $sql->getSql(), $sql->getBindValues());
	}

	public function createClassQuery($class, $sql = NULL, $params = array()) {
		$query = new Query($this, $sql, $params);
		$query->setClass($class);

		return $query;
	}

	public function createSelectSqlBuilder($sql = NULL, $params = array()) {
		return new SqlBuilder\Select;
	}

}
