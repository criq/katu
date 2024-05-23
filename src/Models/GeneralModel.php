<?php

namespace Katu\Models;

abstract class GeneralModel
{
	protected $_manager;

	public function setManager(GeneralManager $manager): GeneralModel
	{
		$this->_manager = $manager;

		return $this;
	}

	public function getManager(): GeneralManager
	{
		return $this->_manager;
	}

	public function getConnection(): \Katu\PDO\Connection
	{
		return $this->getManager()->getConnection();
	}

	public function getTable(): \Katu\PDO\Table
	{
		return $this->getManager()->getTable();
	}

	public function getColumn($name): \Katu\PDO\Column
	{
		$columnClassName = $this->getManager()->getColumnClass()->getName();

		return new $columnClassName($this->getTable(), new \Katu\PDO\Name($name));
	}
}
