<?php

namespace Katu\Models;

use Katu\Types\TClass;
use Sexy\Sexy as SX;

abstract class GeneralManager
{
	abstract public function getDatabaseName(): \Katu\PDO\Name;
	abstract public function getObjectClass(): \Katu\Types\TClass;
	abstract public function getTableName(): \Katu\PDO\Name;

	public function getTableClass(): TClass
	{
		return new TClass("Katu\PDO\Table");
	}

	public function getColumnClass(): TClass
	{
		return new TClass("Katu\PDO\Column");
	}

	public function getConnection(): \Katu\PDO\Connection
	{
		return \Katu\PDO\Connection::getInstance($this->getDatabaseName());
	}

	public function getTable(): \Katu\PDO\Table
	{
		return $this->getConnection()->getTable($this->getTableName());
	}

	public function getColumn($name): \Katu\PDO\Column
	{
		$columnClassName = $this->getColumnClass()->getName();

		return new $columnClassName($this->getTable(), new \Katu\PDO\Name($name));
	}

	public function select(): \Katu\PDO\Query
	{
		$factory = new \Katu\Tools\Factories\ClassFactory($this->getObjectClass());

		// Sexy SQL expression.
		if (count(func_get_args()) == 1 && func_get_arg(0) instanceof \Sexy\Expression) {
			$query = $this->getConnection()->select(func_get_arg(0))->setFactory($factory);

		// Raw SQL and bind values.
		} elseif (count(func_get_args()) == 2) {
			$query = $this->getConnection()->select(func_get_arg(0), func_get_arg(1))->setFactory($factory);

		// Raw SQL.
		} elseif (count(func_get_args()) == 1) {
			$query = $this->getConnection()->select(func_get_arg(0))->setFactory($factory);

		// Anything else.
		} else {
			throw new \Katu\Exceptions\InputErrorException("Invalid arguments passed to query.");
		}

		// echo ($query->sql);die;

		return $query;
	}

	public function getBy(?array $where = [], $orderBy = null, $limitOrPage = null): \Katu\PDO\Result
	{
		$sql = SX::select()
			->from($this->getTable())
			;

		foreach ((array)$where as $name => $value) {
			if ($value instanceof \Sexy\Expression) {
				$sql->where($value);
			} elseif (is_null($value)) {
				$sql->where(SX::cmpIsNull($this->getColumn($name)));
			} else {
				$sql->where(SX::eq($this->getColumn($name), $value));
			}
		}

		if ($orderBy instanceof \Sexy\Expression) {
			$sql->orderBy($orderBy);
		} elseif (is_array($orderBy)) {
			foreach ($orderBy as $_orderBy) {
				$sql->orderBy($_orderBy);
			}
		}

		if ($limitOrPage instanceof \Sexy\Limit) {
			$sql->setLimit($limitOrPage);
			if ($limitOrPage->getLimit() == 1 && (int)$limitOrPage->getOffset() == 0) {
				$sql->setGetFoundRows(false);
			}
		} elseif ($limitOrPage instanceof \Sexy\Page) {
			$sql->setPage($limitOrPage);
			if ($limitOrPage->getPage() == 1 && $limitOrPage->getPerPage()) {
				$sql->setGetFoundRows(false);
			}
		}

		$query = $this->getConnection()->select($sql);
		$query->setFactory(new \Katu\Tools\Factories\ClassFactory($this->getObjectClass()));

		return $query->getResult();
	}

	public function getBySQL(\Sexy\Select $sql): \Katu\PDO\Result
	{
		return $this->select($sql)->getResult();
	}

	public function getOneBySQL(\Sexy\Select $sql)
	{
		return $this->getBySQL($sql->setGetFoundRows(false))->getOne();
	}

	public function getOneBy(?array $where = [], $orderBy = null)
	{
		return $this->getBy($where, $orderBy, SX::page(1, 1))->getOne();
	}

	public function getAll($orderBy = null): \Katu\PDO\Result
	{
		return $this->getBy(null, $orderBy);
	}
}
