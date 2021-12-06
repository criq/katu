<?php

namespace Katu\Models;

use Katu\Types\TClass;
use Sexy\Sexy as SX;

abstract class Base
{
	const DATABASE = 'app';
	const TABLE = null;

	public static function createFromArray(array $array): Base
	{
		$object = new static;
		foreach ($array as $key => $value) {
			$object->$key = $value;
		}

		return $object;
	}

	public static function getTableClass(): TClass
	{
		return new TClass("Katu\PDO\Table");
	}

	public static function getColumnClass(): TClass
	{
		return new TClass("Katu\PDO\Column");
	}

	public static function getClass(): TClass
	{
		return new TClass(get_called_class());
	}

	public function getClassMethods(): array
	{
		return get_class_methods($this);
	}

	public static function getConnection(): \Katu\PDO\Connection
	{
		if (!defined('static::DATABASE')) {
			throw new \Katu\Exceptions\Exception("Undefined database.");
		}

		return \Katu\PDO\Connection::getInstance(static::DATABASE);
	}

	public static function getTableName(): \Katu\PDO\Name
	{
		if (!defined('static::TABLE')) {
			throw new \Katu\Exceptions\Exception("Undefined table.");
		}

		return new \Katu\PDO\Name(static::TABLE);
	}

	public static function getTable(): \Katu\PDO\Table
	{
		$tableClass = (string)static::getTableClass()->getName();

		return new $tableClass(static::getConnection(), static::getTableName());
	}

	public static function getColumn(string $name): \Katu\PDO\Column
	{
		$columnClass = static::getColumnClass()->getName();

		return new $columnClass(static::getTable(), new \Katu\PDO\Name($name));
	}

	public static function select(): \Katu\PDO\Query
	{
		$factory = new \Katu\Tools\Factories\ClassFactory(static::getClass());

		// Sexy SQL expression.
		if (count(func_get_args()) == 1 && func_get_arg(0) instanceof \Sexy\Expression) {
			$query = static::getConnection()->select(func_get_arg(0))->setFactory($factory);

		// Raw SQL and bind values.
		} elseif (count(func_get_args()) == 2) {
			$query = static::getConnection()->select(func_get_arg(0), func_get_arg(1))->setFactory($factory);

		// Raw SQL.
		} elseif (count(func_get_args()) == 1) {
			$query = static::getConnection()->select(func_get_arg(0))->setFactory($factory);

		// Anything else.
		} else {
			throw new \Katu\Exceptions\InputErrorException("Invalid arguments passed to query.");
		}

		// echo ($query->sql);die;

		return $query;
	}

	public static function transaction()
	{
		return static::getConnection()->transaction(...func_get_args());
	}

	public static function filterParams(array $params)
	{
		$filteredParams = [];
		foreach ($params as $param => $value) {
			if (is_string($param)) {
				$filteredParams[$param] = $value;
			}
		}

		return $filteredParams;
	}

	public static function getBy(?array $where = [], $orderBy = null, $limitOrPage = null): \Katu\PDO\Result
	{
		$sql = SX::select();
		$sql->from(static::getTable());

		foreach ($where as $name => $value) {
			if ($value instanceof \Sexy\Expression) {
				$sql->where($value);
			} elseif (is_null($value)) {
				$sql->where(SX::cmpIsNull(static::getColumn($name)));
			} else {
				$sql->where(SX::eq(static::getColumn($name), $value));
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
		} elseif ($limitOrPage instanceof \Sexy\Page) {
			$sql->setPage($limitOrPage);
		}

		$query = static::getConnection()->select($sql);
		$query->setFactory(new \Katu\Tools\Factories\ClassFactory(static::getClass()));

		return $query->getResult();
	}

	public static function getBySql(\Sexy\Select $sql): \Katu\PDO\Result
	{
		return static::select($sql)->getResult();
	}

	public static function getOneBySql(\Sexy\Select $sql)
	{
		return static::getBySql($sql->setGetFoundRows(false))->getOne();
	}

	public static function getOneBy(?array $where = [], $orderBy = null)
	{
		return static::getBy($where, $orderBy)->getOne();
	}

	public static function getAll($orderBy = null): \Katu\PDO\Result
	{
		return static::getBy([], $orderBy);
	}
}
