<?php

namespace Katu;

class ReadOnlyModel {

	public function __call($name, $args) {
		// Bind getter.
		if (preg_match('#^get(?<property>[a-z]+)$#i', $name, $match) && count($args) == 0) {
			$object = $this->getBoundObject($match['property']);
			if ($object) {
				return $object;
			}
		}

		trigger_error('Undeclared class method ' . $name . '.');
	}

	static function getClass() {
		return get_called_class();
	}

	static function getPdo() {
		if (!defined('static::DATABASE')) {
			throw new \Exception("Undefined database.");
		}

		return Pdo\Connection::getInstance(static::DATABASE);
	}

	static function getTable() {
		if (!defined('static::TABLE')) {
			throw new \Exception("Undefined table.");
		}

		return new Pdo\Table(static::getPdo(), static::TABLE);
	}

	static function getColumn($name) {
		return new Pdo\Column(static::getTable(), $name);
	}

	static function createQuery() {
		// Sql expression.
		if (
			count(func_get_args()) == 1
			&& func_get_arg(0) instanceof Pdo\Expression
		) {

			$query = static::getPdo()->createClassQueryFromSql(static::getClass(), func_get_arg(0));

		// Raw sql and bind values.
		} elseif (
			count(func_get_args()) == 2
		) {

			$query = static::getPdo()->createClassQuery(static::getClass(), func_get_arg(0), func_get_arg(1));

		// Raw sql.
		} elseif (
			count(func_get_args()) == 1
		) {

			$query = static::getPdo()->createClassQuery(static::getClass(), func_get_arg(0));

		} else {

			throw new \Katu\Exceptions\ArgumentErrorException("Invalid arguments passed to query.");

		}

		return $query;
	}

	static function getIdColumnName() {
		foreach (static::getPdo()->createQuery(" DESCRIBE " . static::getTable())->getResult() as $row) {
			if (isset($row['Key']) && $row['Key'] == 'PRI') {
				return $row['Field'];
			}
		}

		return FALSE;
	}

	public function getId() {
		return $this->{static::getIdColumnName()};
	}

	static function filterParams($params) {
		$_params = array();

		foreach ($params as $param => $value) {
			if (is_string($param)) {
				$_params[$param] = $value;
			}
		}

		return $_params;
	}

	static function getBy($params = array(), $options = array()) {
		$pdo = static::getPdo();
		$query = $pdo->createQuery();
		$query->setClass(static::getClass());

		$sql = new Pdo\Expressions\Select();
		$sql->setOptions($options);
		$sql->from(static::getTable());

		foreach ($params as $name => $value) {
			if ($value instanceof Pdo\Expression) {
				$sql->where($value);
			} else {
				$sql->where(new Pdo\Expressions\CmpEq(static::getColumn($name), $value));
			}
		}

		$query->setFromSql($sql);

		return $query->getResult();
	}

	static function get($primaryKey) {
		return static::getOneBy(array(static::getIdColumnName() => $primaryKey));
	}

	static function getOneBy() {
		return call_user_func_array(array('static', 'getBy'), array_merge(func_get_args(), array(array(new Pdo\Expressions\Page(1, 1)))))->getOne();
	}

	static function getAll($options = array()) {
		return static::getBy(array(), $options);
	}

	static function getOneOrCreateWithArray($getBy, $array = array()) {
		$object = static::getOneBy($getBy);
		if (!$object) {
			$properties = array_merge($getBy, $array);
			$object = static::create($properties);
		}

		return $object;
	}

	static function getOneOrCreateWithList($getBy) {
		$object = static::getOneBy($getBy);
		if (!$object) {
			$object = call_user_func_array(array('static', 'create'), array_slice(func_get_args(), 1));
		}

		return $object;
	}

	static function getFromAssoc($array) {
		if (!$array) {
			return FALSE;
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
			return preg_match('#^(?<property>[a-zA-Z_]+)_?[Ii][Dd]$#', $i) ? $i : NULL;
		}, static::getTable()->getColumnNames())));
	}

	public function getBoundObject($model) {
		$nsModel = '\\App\\Models\\' . $model;
		if (!class_exists($nsModel)) {
			return FALSE;
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

		return FALSE;
	}

	static function getPropertyName($property) {
		$properties = array_merge(array_keys(get_class_vars(get_called_class())), static::getTable()->getColumnNames());

		foreach ($properties as $_property) {
			if (strtolower($_property) === strtolower($property)) {
				return $_property;
			}
		}

		return FALSE;
	}

	static function getColumnUniqueId($column) {
		$columns = static::getColumnDescriptions();
		if (!$columns[$column]->length) {
			throw new \Exception("Unable to get column length.");
		}

		do {

			$string = \Katu\Utils\Random::getIDString($columns[$column]->length);

		} while (static::getOneBy(array($column => $string)));

		return $string;
	}

}