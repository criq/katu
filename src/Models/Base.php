<?php

namespace Katu\Models;

use \Sexy\Sexy as SX;

class Base {

	const DATABASE = 'app';

	public function __toString() {
		return (string) $this->getId();
	}

	public function __call($name, $args) {
		// Bind getter.
		if (preg_match('#^get(?<property>[a-z]+)$#i', $name, $match) && count($args) == 0) {
			return $this->getBoundObject($match['property']);
		}

		trigger_error('Undeclared class method ' . $name . '.');
	}

	static function getClass() {
		return get_called_class();
	}

	static function getTopClass() {
		return "\\" . ltrim(static::getClass(), "\\");
	}

	static function getAppClass() {
		return implode(array_slice(explode('\\', static::getClass()), -1, 1));
	}

	public function getClassMethods() {
		return get_class_methods($this);
	}

	static function getPDO() {
		if (!defined('static::DATABASE')) {
			throw new \Exception("Undefined database.");
		}

		return PDO\Connection::getInstance(static::DATABASE);
	}

	static function getTableName() {
		if (!defined('static::TABLE')) {
			throw new \Exception("Undefined table.");
		}

		return static::TABLE;
	}

	static function getTable() {
		return new PDO\Table(static::getPDO(), static::getTableName());
	}

	static function getColumn($name) {
		return new PDO\Column(static::getTable(), $name);
	}

	static function createQuery() {
		// Sql expression.
		if (
			count(func_get_args()) == 1
			&& func_get_arg(0) instanceof \Sexy\Expression
		) {

			$query = static::getPDO()->createClassQueryFromSql(static::getClass(), func_get_arg(0));

		// Raw sql and bind values.
		} elseif (
			count(func_get_args()) == 2
		) {

			$query = static::getPDO()->createClassQuery(static::getClass(), func_get_arg(0), func_get_arg(1));

		// Raw sql.
		} elseif (
			count(func_get_args()) == 1
		) {

			$query = static::getPDO()->createClassQuery(static::getClass(), func_get_arg(0));

		} else {

			throw new \Katu\Exceptions\InputErrorException("Invalid arguments passed to query.");

		}

		return $query;
	}

	static function transaction($callback) {
		return call_user_func_array([static::getPDO(), 'transaction'], func_get_args());
	}

	static function filterParams($params) {
		$_params = [];

		foreach ($params as $param => $value) {
			if (is_string($param)) {
				$_params[$param] = $value;
			}
		}

		return $_params;
	}

	static function getBy($params = [], $expressions = [], $options = []) {
		$pdo = static::getPDO();
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

	static function getBySql($sql) {
		return static::createQuery($sql)->getResult();
	}

	static function getCachedBySql($sql, $timeout = null) {
		return Utils\Cache::get(function($sql) {
			return PDO\Results\CachedClassResult::createFromClassResult(static::getBySql($sql));
		}, $timeout, $sql);
	}

	static function getOneBySql($sql) {
		return static::getBySql($sql)->getOne();
	}

	static function getOneBy() {
		$args = array_merge(func_get_args(), [['page' => SX::page(1, 1)]], [['setOptGetTotalRows' => false]]);

		return call_user_func_array(['static', 'getBy'], $args)->getOne();
	}

	static function getAll($expressions = []) {
		return static::getBy([], $expressions);
	}

	static function getFromAssoc($array) {
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

	static function getIdProperties() {
		return array_values(array_filter(array_map(function($i) {
			return preg_match('#^(?<property>[a-zA-Z_]+)_?[Ii][Dd]$#', $i) ? $i : null;
		}, static::getTable()->getColumnNames())));
	}

	public function getBoundObject($model) {
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

	static function getPropertyName($property) {
		$properties = array_merge(array_keys(get_class_vars(get_called_class())), static::getTable()->getColumnNames());

		foreach ($properties as $_property) {
			if (strtolower($_property) === strtolower($property)) {
				return $_property;
			}
		}

		return false;
	}

}
