<?php

namespace Katu\PDO\Results;

class Result extends \ArrayObject
{
	protected $connection;
	protected $factory;
	protected $fetched = false;
	protected $statement;

	public function __construct(\Katu\PDO\Connection $connection, \PDOStatement $statement, \Katu\Interfaces\Factory $factory)
	{
		$this->setConnection($connection);
		$this->setStatement($statement);
		$this->setFactory($factory);

		$errorInfo = $this->getStatement()->errorInfo();
		if ((int)$errorInfo[1]) {
			$exception = new \Katu\Exceptions\Exception($errorInfo[2], $errorInfo[1]);

			// Table doesn't exist.
			if ($errorInfo[1] == 1146 && preg_match("/Table '(.+)\.(?<tableName>.+)' doesn't exist/", $errorInfo[2], $match)) {
				// Create the table.
				$sqlFile = new \Katu\Files\File(__DIR__, '..', '..', 'Tools', 'SQL', $match['tableName'] . '.create.sql');
				if ($sqlFile->exists()) {
					// There is a file, let's create the table.
					$this->getConnection()->createQuery($sqlFile->get())->getResult();
				} else {
					throw $exception;
				}
			} else {
				throw $exception;
			}
		}

		$this->setStorage();
	}

	public function setConnection(\Katu\PDO\Connection $connection) : Result
	{
		$this->connection = $connection;

		return $this;
	}

	public function getConnection() : \Katu\PDO\Connection
	{
		return $this->connection;
	}

	public function setStatement(\PDOStatement $statement) : Result
	{
		$this->statement = $statement;

		return $this;
	}

	public function getStatement() : \PDOStatement
	{
		return $this->statement;
	}

	public function setFactory(\Katu\Interfaces\Factory $factory) : Result
	{
		$this->factory = $factory;

		return $this;
	}

	public function getFactory() : \Katu\Interfaces\Factory
	{
		return $this->factory;
	}

	public function setStorage() : Result
	{
		if (!$this->fetched) {
			foreach ($this->getStatement()->fetchAll(\PDO::FETCH_ASSOC) as $row) {
				$this[] = $this->getFactory()->create($row);
			}
			$this->fetched = true;
		}

		return $this;
	}

	public function getItems() : array
	{
		$this->setStorage();

		return $this->getArrayCopy();
	}

	public function getOne()
	{
		return $this[0] ?? null;
	}

	public function getTotal() : int
	{
		return count($this);
	}

	public function each($callback)
	{
		$res = [];
		foreach ($this->getItems() as $item) {
			if (is_string($callback) && method_exists($item, $callback)) {
				$res[] = call_user_func_array([$item, $callback], [$item]);
			} else {
				$res[] = call_user_func_array($callback, [$item]);
			}
		}

		return $res;
	}

	public function getColumnValues($column)
	{
		$values = [];
		foreach ($this->getItems() as $item) {
			if (is_object($item)) {
				$values[] = $item->$column;
			} else {
				$values[] = $item[$column];
			}
		}

		return $values;
	}

	/****************************************************************************
	 * REST.
	 */
	public function getResponseArray()
	{
		$res = [];
		foreach ($this as $object) {
			$res[] = $object->getResponseArray();
		}

		return $res;
	}
}
