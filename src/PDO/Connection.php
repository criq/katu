<?php

namespace Katu\PDO;

use App\Config\DatabaseConfig;
use Katu\Config\DatabaseConnectionConfig;
use Katu\Tools\Calendar\Timeout;

class Connection
{
	protected $config;
	protected $pdo;
	protected $sessionId;
	protected $title;
	protected static $connections = [];

	public function __construct(string $title)
	{
		$this->setTitle($title);
		$this->setSessionId(implode(".", [
			$this->getTitle(),
			\Katu\Tools\Random\Generator::getString(16),
		]));

		$config = (new DatabaseConfig)->getDatabaseConnectionConfigs()->filterByTitle($this->getTitle())->getFirst();
		if (!$config) {
			throw new \Katu\Exceptions\PDOConfigException("Missing PDO config for instance \"{$title}\".");
		}

		$this->setConfig($config);

		// Build PDO options with defaults for better performance.
		// Note: EMULATE_PREPARES must be true to allow the same named parameter
		// to appear multiple times in a query (e.g., when subqueries are reused).
		$pdoOptions = array_replace([
			\PDO::ATTR_PERSISTENT => $this->getConfig()->getIsPersistent(),
			\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
			\PDO::ATTR_EMULATE_PREPARES => true,
			\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
		], $this->getConfig()->getPdoOptions());

		// Try to connect.
		for ($i = 1; $i <= 3; $i++) {
			try {
				$this->setPdo(new \PDO(
					$this->getConfig()->getPDODSN(),
					$this->getConfig()->getUser(),
					$this->getConfig()->getPlainPassword(),
					$pdoOptions
				));
				break;
			} catch (\Throwable $e) {
				if (strpos($e->getMessage(), "driver does not support setting attributes.")) {
					$pdoOptions = [];
				}
			}
		}
	}

	public function __sleep()
	{
		return ["title", "config"];
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

	public function setConfig(DatabaseConnectionConfig $config): Connection
	{
		$this->config = $config;

		return $this;
	}

	public function getConfig(): DatabaseConnectionConfig
	{
		return $this->config;
	}

	public function setTitle(string $title): Connection
	{
		$this->title = $title;

		return $this;
	}

	public function getTitle(): string
	{
		return (string)$this->title;
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

	public static function getInstance(string $title): Connection
	{
		if (!(static::$connections[$title] ?? null)) {
			static::$connections[$title] = new static($title);
		}

		return static::$connections[$title];
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

	public function getTable(Name $title): Table
	{
		return new Table($this, $title);
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
		// Use a shared context for both SQL generation and param collection
		// to ensure parameter names are consistent.
		$context = [];
		$sql = $select->getSql($context);
		$selectParams = $context["params"] ?? [];

		$query = new Query($this, $sql, array_merge($selectParams, $params));
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

	public function getSQLModes(): array
	{
		$sql = " SELECT @@SESSION.sql_mode AS sql_mode ";
		$array = explode(",", $this->createQuery($sql)->getResult()->getItems()[0]["sql_mode"] ?? null);

		return array_combine($array, $array);
	}

	public function setSQLModes(array $sqlModes)
	{
		$sql = " SET @@SESSION.sql_mode = :sqlMode ";
		$res = $this->createQuery($sql, [
			"sqlMode" => implode(",", $sqlModes),
		])->getResult();

		return $res;
	}

	public function addSQLMode($sqlMode)
	{
		$sqlModes = array_merge($this->getSQLModes(), [$sqlMode]);

		return $this->setSQLModes($sqlModes);
	}

	public function removeSQLMode($sqlMode)
	{
		$sqlModes = $this->getSQLModes();
		if ($sqlModes[$sqlMode] ?? null) {
			unset($sqlModes[$sqlMode]);
		}

		return $this->setSQLModes($sqlModes);
	}

	public function getFoundRowsPickledResult($sql, \Katu\Tools\Factories\FactoryInterface $factory, \Katu\Cache\Pickle $pickle, \Katu\Tools\Calendar\Timeout $timeout)
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
