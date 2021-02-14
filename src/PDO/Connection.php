<?php

namespace Katu\PDO;

class Connection
{
	protected $config;
	protected $connection;
	protected $name;
	protected static $connections = [];

	public function __construct($name)
	{
		$this->name = $name;

		try {
			$this->config = Config::createFromConfig(\Katu\Config\Config::get('db', $name));
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			throw new \Katu\Exceptions\PDOConfigException("Missing PDO config for instance " . $name . ".");
		}

		// Try to connect.
		for ($i = 1; $i <= 3; $i++) {
			try {
				$this->connection = new \PDO($this->config->getPDODSN(), $this->config->user, $this->config->password);
				// $this->connection->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
				break;
			} catch (\Throwable $e) {
				if (strpos($e->getMessage(), 'driver does not support setting attributes.')) {
					$attributes = null;
				}
			}
		}
	}

	public function __sleep()
	{
		return ['name', 'config'];
	}

	public function getConnection()
	{
		return $this->connection;
	}

	public function getConfig()
	{
		return $this->config;
	}

	public function getName() : string
	{
		return (string)$this->name;
	}

	public function getVersion() : string
	{
		return (string)$this->connection->getAttribute(\PDO::ATTR_SERVER_VERSION);
	}

	public static function getInstance($name)
	{
		if (!(static::$connections[$name] ?? null)) {
			static::$connections[$name] = new static($name);
		}

		return static::$connections[$name];
	}

	public function getLastInsertId()
	{
		return $this->connection->lastInsertId();
	}

	public function tableExists(Name $tableName)
	{
		return in_array($tableName, $this->getTableNames());
	}

	public function getTables()
	{
		$connection = $this;

		return array_map(function ($tableName) use ($connection) {
			return new Table($connection, $tableName);
		}, $this->getTableNames());
	}

	public function getTable($tableName)
	{
		return new Table($this, $tableName);
	}

	public function getTableNames()
	{
		$sql = " SHOW TABLES ";
		$res = $this->createQuery($sql)->getResult()->getItems();

		return array_map(function ($i) {
			$names = array_values($i);
			return new \Katu\PDO\Name($names[0]);
		}, $res);
	}

	public function getViews()
	{
		$connection = $this;

		return array_map(function ($i) use ($connection) {
			return new View($connection, $i);
		}, $this->getViewNames());
	}

	public function getViewNames()
	{
		$sql = " SHOW FULL TABLES IN " . $this->config->database . " WHERE TABLE_TYPE LIKE 'VIEW' ";
		$res = $this->createQuery($sql)->getResult()->getItems();

		return array_map(function ($i) {
			$names = array_values($i);
			return $names[0];
		}, $res);
	}

	public function getViewReport()
	{
		$views = [];

		foreach ($this->getViews() as $view) {
			$views[$view->name->name]['usedIn'] = $view->getUsedInViews();
			$views[$view->name->name]['usage'] = $view->getTotalUsage();
		}

		return $views;
	}

	public function select(\Sexy\Select $select, array $params = []) : Query
	{
		$query = new Query($this, $select->getSql(), array_merge($select->getParams(), $params));
		if ($select->getPage()) {
			$query->setPage($select->getPage());
		}

		return $query;
	}

	public function createQuery($sql, array $params = []) : Query
	{
		return new Query($this, $sql, $params);
	}

	public function transaction($callback)
	{
		try {
			$this->begin();
			$res = call_user_func_array($callback, array_slice(func_get_args(), 1));
			$this->commit();

			return $res;
		} catch (\Exception $e) {
			$this->rollback();
			throw $e;
		}
	}

	public function begin()
	{
		return $this->connection->beginTransaction();
	}

	public function commit()
	{
		return $this->connection->commit();
	}

	public function rollback()
	{
		return $this->connection->rollBack();
	}

	public function getSqlModes() : array
	{
		$sql = " SELECT @@SESSION.sql_mode AS sql_mode ";
		$array = explode(',', $this->createQuery($sql)->getResult()->getItems()[0]['sql_mode'] ?? null);

		return array_combine($array, $array);
	}

	public function setSqlModes(array $sqlModes)
	{
		$sql = " SET @@SESSION.sql_mode = :sqlMode ";
		$res = $this->createQuery($sql, [
			'sqlMode' => implode(',', $sqlModes),
		])->getResult();

		return $res;
	}

	public function addSqlMode($sqlMode)
	{
		$sqlModes = array_merge($this->getSqlModes(), [$sqlMode]);

		return $this->setSqlModes($sqlModes);
	}

	public function removeSqlMode($sqlMode)
	{
		$sqlModes = $this->getSqlModes();
		if ($sqlModes[$sqlMode] ?? null) {
			unset($sqlModes[$sqlMode]);
		}

		return $this->setSqlModes($sqlModes);
	}
}
