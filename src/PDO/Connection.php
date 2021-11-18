<?php

namespace Katu\PDO;

use Katu\Tools\DateTime\Timeout;

class Connection
{
	protected $config;
	protected $pdo;
	protected $name;
	protected $sessionId;
	protected static $connections = [];

	public function __construct(string $name)
	{
		$this->setName($name);
		$this->setSessionId(implode('.', [
			$this->getName(),
			\Katu\Tools\Random\Generator::getString(16),
		]));

		try {
			$this->config = Config::createFromConfig(\Katu\Config\Config::get('db', $name));
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			throw new \Katu\Exceptions\PDOConfigException("Missing PDO config for instance " . $name . ".");
		}

		// Try to connect.
		for ($i = 1; $i <= 3; $i++) {
			try {
				$this->setPdo(new \PDO($this->config->getPDODSN(), $this->config->user, $this->config->password));
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

	public function setPdo(\PDO $pdo): Connection
	{
		$this->pdo = $pdo;

		return $this;
	}

	public function getPdo(): \PDO
	{
		return $this->pdo;
	}

	public function getConfig(): Config
	{
		return $this->config;
	}

	public function setName(string $name): Connection
	{
		$this->name = $name;

		return $this;
	}

	public function getName(): string
	{
		return (string)$this->name;
	}

	public function setSessionId(string $sessionId): Connection
	{
		$this->sessionId = $sessionId;

		return $this;
	}

	public function getSessionId(): string
	{
		return $this->sessionId;
	}

	public function getVersion(): string
	{
		return (string)$this->getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION);
	}

	public static function getInstance($name): Connection
	{
		if (!(static::$connections[$name] ?? null)) {
			static::$connections[$name] = new static($name);
		}

		return static::$connections[$name];
	}

	public function getLastInsertId()
	{
		return $this->getPdo()->lastInsertId();
	}

	public function tableExists(Name $tableName): bool
	{
		return in_array($tableName, $this->getTableNames());
	}

	public function getTables(): array
	{
		$connection = $this;

		return array_map(function ($tableName) use ($connection) {
			return new Table($connection, $tableName);
		}, $this->getTableNames());
	}

	public function getTable($tableName): Table
	{
		return new Table($this, $tableName);
	}

	public function getTableNames(): array
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
			$views[$view->name->name]['usage'] = $view->getTotalUsage(new Timeout('1 day'));
		}

		return $views;
	}

	public function select(\Sexy\Select $select, array $params = []): Query
	{
		$query = new Query($this, $select->getSql(), array_merge($select->getParams(), $params));
		if ($select->getPage()) {
			$query->setPage($select->getPage());
		}

		return $query;
	}

	public function createQuery($sql, array $params = []): Query
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
		} catch (\Throwable $e) {
			$this->rollback();
			throw $e;
		}
	}

	public function begin()
	{
		return $this->getPdo()->beginTransaction();
	}

	public function commit()
	{
		return $this->getPdo()->commit();
	}

	public function rollback()
	{
		return $this->getPdo()->rollBack();
	}

	public function getSqlModes(): array
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

	public function getFoundRowsPickledResult($sql, \Katu\Interfaces\Factory $factory, \Katu\Cache\Pickle $pickle, \Katu\Tools\DateTime\Timeout $timeout)
	{
		if ($pickle->isValid($timeout)) {
			$sql->setGetFoundRows(false);
			$result = $this->createQuery($sql)->setFactory($factory)->setFoundRows($pickle->get())->getResult();
		} else {
			$sql->setGetFoundRows(true);
			$result = $this->createQuery($sql)->setFactory($factory)->getResult();
			$pickle->set($result->getTotal());
		}

		return $result;
	}

	public function getProcesslist(): array
	{
		$sql = " SHOW FULL PROCESSLIST ";

		return $this->createQuery($sql)->getResult()->getItems();
	}
}
