<?php

namespace Katu\PDO;

use \PDO;

class Connection
{
	public $config;
	public $connection;
	public $name;
	public static $connections = [];

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
				$this->connection = new PDO($this->config->getPDODSN(), $this->config->user, $this->config->password);
				break;
			} catch (\ErrorException $e) {
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

	public function getName()
	{
		return $this->name;
	}

	public static function getInstance($name)
	{
		if (!isset(static::$connections[$name])) {
			static::$connections[$name] = new self($name);
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
		$pdo = $this;

		return array_map(function ($tableName) use ($pdo) {
			return new Table($pdo, $tableName);
		}, $this->getTableNames());
	}

	public function getTable($tableName)
	{
		return new Table($this, $tableName);
	}

	public function getTableNames()
	{
		return \Katu\Cache\Runtime::get(['pdo', $this->name, 'tables'], function () {
			$sql = " SHOW TABLES ";
			$res = $this->createQuery($sql)->getResult()->getArray();

			return array_map(function ($i) {
				$names = array_values($i);
				return new \Katu\PDO\Name($names[0]);
			}, $res);
		});
	}

	public function getViews()
	{
		$pdo = $this;

		return array_map(function ($i) use ($pdo) {
			return new View($pdo, $i);
		}, $this->getViewNames());
	}

	public function getViewNames()
	{
		return \Katu\Cache\Runtime::get(['pdo', $this->name, 'views'], function () {
			$sql = " SHOW FULL TABLES IN " . $this->config->database . " WHERE TABLE_TYPE LIKE 'VIEW' ";
			$res = $this->createQuery($sql)->getResult()->getArray();

			return array_map(function ($i) {
				$names = array_values($i);
				return $names[0];
			}, $res);
		});
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

	public function createQuery($sql = null, $params = [])
	{
		$query = new Query($this, $sql, $params);

		return $query;
	}

	public function createQueryFromSql(\Sexy\Expression $sql, array $params = [])
	{
		$query = new Query($this);
		$query->setFromSql($sql);
		$query->setBindValues($params);

		return $query;
	}

	public function createClassQuery($class, $sql = null, array $params = [])
	{
		$query = new Query($this, $sql, $params);
		$query->setClass($class);

		return $query;
	}

	public function createClassQueryFromSql($class, \Sexy\Expression $sql)
	{
		$query = static::createQueryFromSql($sql);
		$query->setClass($class);

		return $query;
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

	/* public function dump($options = []) {
		$extension = 'sql';
		$dumpOptions = [];

		if (isset($options['compress']) && $options['compress'] == 'gzip') {
			$extension = 'sql.gz';
			$dumpOptions['compress'] = 'Gzip';
		}

		if (isset($options['fileName']) && $options['fileName']) {
			$fileName = $options['fileName'];
		} else {
			$fileName = \Katu\File\System::joinPaths(\Katu\App::getBaseDir(), 'databases', $this->config->database, implode('.', [(new \Katu\Tools\DateTime\DateTime())->format('YmdHis'), $extension]));
		}

		if (isset($options['addDropTable']) && $options['addDropTable']) {
			$dumpOptions['add-drop-table'] = true;
		}

		$dumpOptions['exclude-tables'] = [];

		if (isset($options['skipCache']) && $options['skipCache']) {
			foreach ($this->getTableNames() as $tableName) {
				if (preg_match('#^_cache_#', $tableName)) {
					$dumpOptions['exclude-tables'][] = $tableName;
				}
			}
		}

		try {

			\Katu\Utils\FileSystem::touch($fileName);

			$dump = new \Ifsnop\Mysqldump\Mysqldump($this->config->database, $this->config->user, $this->config->password, $this->config->host, $this->config->type, $dumpOptions);
			$dump->start($fileName);

		} catch (\Exception $e) {

			\Katu\Errors\Handler::log($e);

			@unlink($fileName);

		}
	} */

	/* public function backup($options = []) {
		return $this->dump(array_merge([
			'skipCache' => true,
			'addDropTable' => true,
			'compress' => 'gzip',
		], $options));
	} */
}
