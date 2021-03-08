<?php

namespace Katu\Models;

use \Sexy\Sexy as SX;

abstract class Base
{
	const DATABASE = 'app';
	const TABLE = null;

	public static function createFromArray(array $array)
	{
		$object = new static;
		foreach ($array as $key => $value) {
			$object->$key = $value;
		}

		return $object;
	}

	public static function getTableClass()
	{
		return new \ReflectionClass("Katu\PDO\Table");
	}

	public static function getColumnClass()
	{
		return new \ReflectionClass("Katu\PDO\Column");
	}

	public static function getClass()
	{
		return get_called_class();
	}

	public static function getClassName()
	{
		return new \Katu\Tools\Classes\ClassName(static::getClass());
	}

	public function getClassMethods()
	{
		return get_class_methods($this);
	}

	public static function getConnection() : \Katu\PDO\Connection
	{
		if (!defined('static::DATABASE')) {
			throw new \Katu\Exceptions\Exception("Undefined database.");
		}

		return \Katu\PDO\Connection::getInstance(static::DATABASE);
	}

	public static function getTableName() : \Katu\PDO\Name
	{
		if (!defined('static::TABLE')) {
			throw new \Katu\Exceptions\Exception("Undefined table.");
		}

		return new \Katu\PDO\Name(static::TABLE);
	}

	public static function getTable() : \Katu\PDO\Table
	{
		$tableClass = (string)static::getTableClass()->getName();

		return new $tableClass(static::getConnection(), static::getTableName());
	}

	public static function getColumn(string $name) : \Katu\PDO\Column
	{
		$columnClass = static::getColumnClass()->getName();

		return new $columnClass(static::getTable(), new \Katu\PDO\Name($name));
	}

	public static function select() : \Katu\PDO\Query
	{
		$factory = new \Katu\Tools\Factories\ClassFactory(static::getClassName());

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

	public static function getBy(?array $params = [], ?array $expressions = [], ?array $options = []) : \Katu\PDO\Results\Result
	{
		$sql = SX::select();
		$sql->addExpressions($expressions);
		$sql->from(static::getTable());

		foreach ($params as $name => $value) {
			if ($value instanceof \Sexy\Expression) {
				$sql->where($value);
			} elseif (is_null($value)) {
				$sql->where(SX::cmpIsNull(static::getColumn($name)));
			} else {
				$sql->where(SX::eq(static::getColumn($name), $value));
			}
		}

		if ($options['setOptGetTotalRows'] ?? null) {
			$sql->setOptGetTotalRows($options['setOptGetTotalRows']);
		}

		$query = static::getConnection()->select($sql);
		$query->setFactory(new \Katu\Tools\Factories\ClassFactory(static::getClassName()));

		return $query->getResult();
	}

	public static function getBySql($sql) : \Katu\PDO\Results\Result
	{
		return static::select($sql)->getResult();
	}

	public static function getOneBySql($sql)
	{
		return static::getBySql($sql)->getOne();
	}

	public static function getOneBy()
	{
		$args = array_merge(func_get_args(), [
			[
				'page' => SX::page(1, 1)
			],
		], [
			[
				'setOptGetTotalRows' => false,
			],
		]);

		return static::getBy(...$args)->getOne();
	}

	public static function getAll(?array $expressions = []) : \Katu\PDO\Results\Result
	{
		return static::getBy([], $expressions);
	}
}
