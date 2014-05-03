<?php

namespace Katu\PDO;

use \PDO;
use \Katu\Config;

class Connection {

	public $name;
	public $connection;

	static $connections = array();

	public function __construct($name) {
		$this->name = $name;

		$config = Config::getDB($name);

		$this->connection = new PDO($config->getPDODSN(), $config->user, $config->password, array(
			PDO::ATTR_PERSISTENT => TRUE,
		));
	}

	static function getInstance($name) {
		if (!isset(static::$connections[$name])) {
			static::$connections[$name] = new self($name);
		}

		return static::$connections[$name];
	}



	public function createQuery($sql = NULL, $params = array()) {
		return new Query($this, $sql, $params);
	}

	public function createClassQuery($class, $sql = NULL, $params = array()) {
		$query = new Query($this, $sql, $params);
		$query->setClass($class);

		return $query;
	}

}
