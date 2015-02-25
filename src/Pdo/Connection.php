<?php

namespace Katu\Pdo;

use \PDO;
use \Katu\Config;

class Connection {

	public $name;
	public $config;
	public $connection;

	static $connections = [];

	public function __construct($name) {
		$this->name = $name;

		try {
			$this->config = Config::getDb($name);
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			throw new \Katu\Exceptions\MissingPdoConfigException("Missing PDO config for instance " . $name . ".");
		}

		// Try to connect.
		for ($i = 1; $i <= 3; $i++) {
			try {
				$this->connection = new PDO($this->config->getPdoDSN(), $this->config->user, $this->config->password, [
					PDO::ATTR_PERSISTENT => true,
				]);
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

	public function getTables() {
		$pdo = $this;

		return array_map(function($i) use($pdo) {
			return new Table($pdo, $i);
		}, $this->getTableNames());
	}

	public function getTableNames() {
		$tables = [];

		$sql = " SHOW TABLES ";
		$res = $this->createQuery($sql)->getResult()->getArray();

		$pdo = $this;

		return array_map(function($i) use($pdo) {
			$names = array_values($i);
			return $names[0];
		}, $res);
	}

	public function tableExists($tableName) {
		return in_array($tableName, $this->getTableNames());
	}

	public function createQuery($sql = null, $params = []) {
		$query = new Query($this, $sql, $params);

		return $query;
	}

	public function createQueryFromSql(\Sexy\Expression $sql) {
		$query = new Query($this);
		$query->setFromSql($sql);

		return $query;
	}

	public function createClassQuery($class, $sql = null, $params = []) {
		$query = new Query($this, $sql, $params);
		$query->setClass($class);

		return $query;
	}

	public function createClassQueryFromSql($class, \Sexy\Expression $sql) {
		$query = static::createQueryFromSql($sql);
		$query->setClass($class);

		return $query;
	}

	public function dump($fileName = null) {
		if (!$fileName) {
			$fileName = \Katu\Utils\FileSystem::joinPaths(BASE_DIR, 'databases', $this->config->database, (new \Katu\Utils\DateTime())->format('YmdHis'));
		}

		\Katu\Utils\FileSystem::touch($fileName);

		try {

			$dump = new \Ifsnop\Mysqldump\Mysqldump($this->config->database, $this->config->user, $this->config->password, $this->config->host, $this->config->type, [
				'add-drop-table' => true,
			]);

			$dump->start($fileName);

		} catch (\Exception $e) {

		}
	}

}
