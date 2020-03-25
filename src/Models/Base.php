<?php

namespace Katu\Models;

use \Sexy\Sexy as SX;

abstract class Base
{
	const DATABASE = 'app';
	const TABLE = null;

	public function __toString()
	{
		return (string) $this->getId();
	}

	public function __call($name, $args)
	{
		// Getter.
		if (preg_match('/^get(?<property>[a-z0-9]+)$/i', $name, $match)) {
			$property = $this->getPropertyName($match['property']);

			// Not found, maybe just added.
			if (!$property) {
				\Katu\Cache\General::clearMemory();
				$property = $this->getPropertyName($match['property']);
			}

			return $this->{$property} ?? null;
		}

		trigger_error('Undeclared class method ' . $name . '.');
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
			throw new \Exception("Undefined database.");
		}

		return \Katu\PDO\Connection::getInstance(static::DATABASE);
	}

	public static function getTableName() : \Katu\PDO\Name
	{
		if (!defined('static::TABLE')) {
			throw new \Exception("Undefined table.");
		}

		return new \Katu\PDO\Name(static::TABLE);
	}

	public static function getTable() : \Katu\PDO\Table
	{
		return new \Katu\PDO\Table(static::getConnection(), static::getTableName());
	}

	public static function getColumn($name) : \Katu\PDO\Column
	{
		return new \Katu\PDO\Column(static::getTable(), new \Katu\PDO\Name($name));
	}

	public static function select() : \Katu\PDO\Query
	{
		// Sexy SQL expression.
		if (count(func_get_args()) == 1 && func_get_arg(0) instanceof \Sexy\Expression) {
			$query = static::getConnection()->createClassQuery(static::getClassName(), func_get_arg(0));

		// Raw SQL and bind values.
		} elseif (count(func_get_args()) == 2) {
			$query = static::getConnection()->createClassQuery(static::getClassName(), func_get_arg(0), func_get_arg(1));

		// Raw SQL.
		} elseif (count(func_get_args()) == 1) {
			$query = static::getConnection()->createClassQuery(static::getClassName(), func_get_arg(0));

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

	public static function filterParams($params)
	{
		$filteredParams = [];

		foreach ($params as $param => $value) {
			if (is_string($param)) {
				$filteredParams[$param] = $value;
			}
		}

		return $filteredParams;
	}

	public static function getBy($params = [], $expressions = [], $options = [])
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

		if (isset($options['setOptGetTotalRows'])) {
			$sql->setOptGetTotalRows($options['setOptGetTotalRows']);
		}

		$query = static::getConnection()->select($sql);
		$query->setClassName(static::getClassName());

		return $query->getResult();
	}

	public static function getBySql($sql)
	{
		return static::select($sql)->getResult();
	}

	// public static function getCachedBySql($sql, $timeout = null)
	// {
	// 	return Utils\Cache::get(function($sql) {
	// 		return PDO\Results\CachedClassResult::createFromClassResult(static::getBySql($sql));
	// 	}, $timeout, $sql);
	// }

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

	public static function getAll($expressions = [])
	{
		return static::getBy([], $expressions);
	}

	public static function getFromAssoc($array)
	{
		if (!$array) {
			return false;
		}

		$class = static::getClass();
		$object = new $class;

		foreach ($array as $key => $value) {
			$object->$key = $value;
		}

		return $object;
	}

	public static function getIdProperties()
	{
		return array_values(array_filter(array_map(function ($i) {
			return preg_match('#^(?<property>[a-zA-Z_]+)_?[Ii][Dd]$#', $i) ? $i : null;
		}, static::getTable()->getColumnNames())));
	}

	// public function getBoundObject($model)
	// {
	// 	$nsModel = '\\App\\Models\\' . $model;
	// 	if (!class_exists($nsModel)) {
	// 		return null;
	// 	}

	// 	foreach (static::getIdProperties() as $property) {
	// 		$proposedModel = '\\App\\Models\\' . ucfirst(preg_replace('#^(.+)_?[Ii][Dd]$#', '\\1', $property));
	// 		if ($proposedModel && $nsModel == $proposedModel) {
	// 			$object = $proposedModel::get($this->{$property});
	// 			if ($object) {
	// 				return $object;
	// 			}
	// 		}
	// 	}

	// 	return null;
	// }

	public static function getPropertyName($property)
	{
		$properties = array_merge(array_keys(get_class_vars(get_called_class())), static::getTable()->getColumnNames());

		foreach ($properties as $p) {
			if (strtolower($p) === strtolower($property)) {
				return $p;
			}
		}

		return false;
	}
}
