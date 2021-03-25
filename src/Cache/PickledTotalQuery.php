<?php

// namespace Katu\Cache;

// class PickledTotalQuery extends \Katu\Cache\Pickle
// {
// 	protected $connection;
// 	protected $factory;
// 	protected $sql;
// 	protected $timeout;

// 	public function setConnection(\Katu\PDO\Connection $connection) : PickledTotalQuery
// 	{
// 		$this->connection = $connection;

// 		return $this;
// 	}

// 	public function getConnection() : ?\Katu\PDO\Connection
// 	{
// 		return $this->connection;
// 	}

// 	public function setSql(\Sexy\Select $sql) : PickledTotalQuery
// 	{
// 		$this->sql = $sql;

// 		return $this;
// 	}

// 	public function getSql() : ?\Sexy\Select
// 	{
// 		return $this->sql;
// 	}

// 	public function setFactory(\Katu\Tools\Factories\Factory $factory) : PickledTotalQuery
// 	{
// 		$this->factory = $factory;

// 		return $this;
// 	}

// 	public function getFactory() : ?\Katu\Tools\Factories\Factory
// 	{
// 		return $this->factory;
// 	}

// 	public function setTimeout($timeout) : PickledTotalQuery
// 	{
// 		$this->timeout = $timeout;

// 		return $this;
// 	}

// 	public function getTimeout()
// 	{
// 		return $this->timeout;
// 	}

// 	public function getResult()
// 	{
// 		if (!$this->isValid($this->getTimeout())) {
// 			$sql = (clone $this->getSql())
// 				->setOptGetTotalRows(true)
// 				;

// 			$query = $this->getConnection()->createQuery($sql)
// 				->setFactory($this->getFactory())
// 				;

// 			$result = $query->getResult();

// 			$this->set($result->getTotal());
// 		} else {
// 			$sql = (clone $this->getSql())
// 				->setOptGetTotalRows(false)
// 				;

// 			$query = $this->getConnection()->createQuery($sql)
// 				->setFactory($this->getFactory())
// 				->setTotal($this->get())
// 				;

// 			$result = $query->getResult();
// 		}

// 		return $result;
// 	}
// }
