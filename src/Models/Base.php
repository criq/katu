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
		// Bind getter.
		if (preg_match('#^get(?<property>[a-z]+)$#i', $name, $match) && count($args) == 0) {
			return $this->getBoundObject($match['property']);
		}

		trigger_error('Undeclared class method ' . $name . '.');
	}

	public static function getClass()
	{
		return get_called_class();
	}

	public static function getTopClass()
	{
		return "\\" . ltrim(static::getClass(), "\\");
	}

	public static function getAppClass()
	{
		return implode(array_slice(explode('\\', static::getClass()), -1, 1));
	}

	public function getClassMethods()
	{
		return get_class_methods($this);
	}

	public static function getConnection()
	{
		if (!defined('static::DATABASE')) {
			throw new \Exception("Undefined database.");
		}

		return \Katu\PDO\Connection::getInstance(static::DATABASE);
	}

	public static function getTableName()
	{
		if (!defined('static::TABLE')) {
			throw new \Exception("Undefined table.");
		}

		return new \Katu\PDO\Name(static::TABLE);
	}

	public static function getTable()
	{
		return new \Katu\PDO\Table(static::getConnection(), static::getTableName());
	}

	public static function getColumn($name)
	{
		return new \Katu\PDO\Column(static::getTable(), new \Katu\PDO\Name($name));
	}

	public static function createQuery()
	{
		// Sql expression.
		if (count(func_get_args()) == 1 && func_get_arg(0) instanceof \Sexy\Expression) {
			$query = static::getConnection()->createClassQueryFromSql(static::getClass(), func_get_arg(0));
		// Raw sql and bind values.
		} elseif (count(func_get_args()) == 2) {
			$query = static::getConnection()->createClassQuery(static::getClass(), func_get_arg(0), func_get_arg(1));
		// Raw sql.
		} elseif (count(func_get_args()) == 1) {
			$query = static::getConnection()->createClassQuery(static::getClass(), func_get_arg(0));
		} else {
			throw new \Katu\Exceptions\InputErrorException("Invalid arguments passed to query.");
		}

		return $query;
	}

	public static function transaction($callback)
	{
		return call_user_func_array([static::getConnection(), 'transaction'], func_get_args());
	}

	public static function filterParams($params)
	{
		$_params = [];

		foreach ($params as $param => $value) {
			if (is_string($param)) {
				$_params[$param] = $value;
			}
		}

		return $_params;
	}

	public static function getBy($params = [], $expressions = [], $options = [])
	{
		$pdo = static::getConnection();
		$query = $pdo->createQuery();
		$query->setClass(static::getClass());

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

		$query->setFromSql($sql);

		return $query->getResult();
	}

	public static function getBySql($sql)
	{
		return static::createQuery($sql)->getResult();
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
		$args = array_merge(func_get_args(), [['page' => SX::page(1, 1)]], [['setOptGetTotalRows' => false]]);

		return call_user_func_array(['static', 'getBy'], $args)->getOne();
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

	public function getBoundObject($model)
	{
		$nsModel = '\\App\\Models\\' . $model;
		if (!class_exists($nsModel)) {
			return null;
		}

		foreach (static::getIdProperties() as $property) {
			$proposedModel = '\\App\\Models\\' . ucfirst(preg_replace('#^(.+)_?[Ii][Dd]$#', '\\1', $property));
			if ($proposedModel && $nsModel == $proposedModel) {
				$object = $proposedModel::get($this->{$property});
				if ($object) {
					return $object;
				}
			}
		}

		return null;
	}

	public static function getPropertyName($property)
	{
		$properties = array_merge(array_keys(get_class_vars(get_called_class())), static::getTable()->getColumnNames());

		foreach ($properties as $_property) {
			if (strtolower($_property) === strtolower($property)) {
				return $_property;
			}
		}

		return false;
	}
}
