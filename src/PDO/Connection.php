<?php

namespace Katu\PDO;

use Katu\Tools\Calendar\Timeout;

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
		$this->setSessionId(implode(".", [
			$this->getName(),
			\Katu\Tools\Random\Generator::getString(16),
		]));

		try {
			$this->setConfig(Config::createFromConfig(\Katu\Config\Config::get("db", $name)));
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			throw new \Katu\Exceptions\PDOConfigException("Missing PDO config for instance '{$name}'.");
		}

		// Try to connect.
		for ($i = 1; $i <= 3; $i++) {
			try {
				$this->setPdo(new \PDO($this->getConfig()->getPDODSN(), $this->getConfig()->getUser(), $this->getConfig()->getPassword()));
				break;
			} catch (\Throwable $e) {
				if (strpos($e->getMessage(), "driver does not support setting attributes.")) {
					$attributes = null;
				}
			}
		}
	}

	public function __sleep()
	{
		return ["name", "config"];
	}

	public function setPdo(\PDO $value): Connection
	{
		$this->pdo = $value;

		return $this;
	}

	public function getPdo(): \PDO
	{
		return $this->pdo;
	}

	public function setConfig(Config $value): Connection
	{
		$this->config = $value;

		return $this;
	}

	public function getConfig(): Config
	{
		return $this->config;
	}

	public function setName(string $value): Connection
	{
		$this->name = $value;

		return $this;
	}

	public function getName(): string
	{
		return (string)$this->name;
	}

	public function setSessionId(string $value): Connection
	{
		$this->sessionId = $value;

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

	public static function getInstance(string $name): Connection
	{
		if (!(static::$connections[$name] ?? null)) {
			static::$connections[$name] = new static($name);
		}

		return static::$connections[$name];
	}

	public function tableExists(Name $tableName): bool
	{
		return in_array($tableName, $this->getTableNames());
	}

	public function getTableNames(): array
	{
		$sql = " SHOW TABLES ";
		$res = $this->createQuery($sql)->getResult()->getItems();

		return array_map(function (array $row) {
			return new Name(array_values($row)[0]);
		}, $res);
	}

	public function getTables(): TableCollection
	{
		$res = new TableCollection;
		foreach ($this->getTableNames() as $tableName) {
			$res[] = new Table($this, $tableName);
		}

		return $res;
	}

	public function getTable(Name $name): Table
	{
		return new Table($this, $name);
	}

	public function getViewNames(): array
	{
		$sql = " SHOW FULL TABLES IN {$this->getConfig()->getDatabase()} WHERE TABLE_TYPE LIKE 'VIEW' ";
		$res = $this->createQuery($sql)->getResult()->getItems();

		return array_map(function ($i) {
			return new Name(array_values($i)[0]);
		}, $res);
	}

	public function getViews(): TableCollection
	{
		$res = new TableCollection;
		foreach ($this->getViewNames() as $viewName) {
			$res[] = new View($this, $viewName);
		}

		return $res;
	}

	public function getViewReport(): array
	{
		$views = [];
		foreach ($this->getViews() as $view) {
			$views[$view->name->name]["usedIn"] = $view->getUsedInViews();
			$views[$view->name->name]["usage"] = $view->getTotalUsage(new Timeout("1 day"));
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
		$array = explode(",", $this->createQuery($sql)->getResult()->getItems()[0]["sql_mode"] ?? null);

		return array_combine($array, $array);
	}

	public function setSqlModes(array $sqlModes)
	{
		$sql = " SET @@SESSION.sql_mode = :sqlMode ";
		$res = $this->createQuery($sql, [
			"sqlMode" => implode(",", $sqlModes),
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

	public function getFoundRowsPickledResult($sql, \Katu\Interfaces\Factory $factory, \Katu\Cache\Pickle $pickle, \Katu\Tools\Calendar\Timeout $timeout)
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

	public function getProcesslist(): Processlist
	{
		$sql = " SHOW FULL PROCESSLIST ";

		return new Processlist(array_map(function (array $item) {
			return new Process($this, $item);
		}, $this->createQuery($sql)->getResult()->getItems()));
	}

	public function getLastInsertId()
	{
		return $this->getPdo()->lastInsertId();
	}
}
