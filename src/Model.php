<?php

namespace Katu;

class Model {

	protected $__updated = FALSE;
	static $__definition = array();

	public function __call($name, $args) {
		// Setter.
		if (preg_match('#^set(?<property>[a-z]+)$#i', $name, $match) && count($args) == 1) {
			$property = $this->getPropertyName($match['property']);
			$value    = $args[0];

			if ($property && $this->update($property, $value)) {
				return TRUE;
			}
		}

		// Bind getter.
		if (preg_match('#^get(?<property>[a-z]+)$#i', $name, $match) && count($args) == 0) {
			$object = $this->getBoundObject($match['property']);
			if ($object) {
				return $object;
			}
		}

		user_error('Undeclared class method ' . $name . '.');
	}

	static function getDB() {
		if (!defined('static::DATABASE')) {
			throw new Exception("Undefined database.");
		}

		return DB\Connection::getInstance(static::DATABASE);
	}

	static function getTable() {
		if (!defined('static::TABLE')) {
			throw new Exception("Undefined table.");
		}

		return static::TABLE;
	}

	static function getClass() {
		return get_called_class();
	}

	static function getColumns() {
		$columns = array();

		foreach (static::getDB()->query(" SHOW COLUMNS FROM " . static::getTable())->fetch_all() as $row) {
			$columns[$row['Field']] = new \Katu\DB\Column($row);
		}

		return $columns;
	}

	static function getColumn($name) {
		$columns = static::getColumns();
		if (!isset($columns[$name])) {
			throw new Exception("Invalid column " . $name . ".");
		}

		return $columns[$name];
	}

	static function getColumnNames() {
		return array_values(array_map(function($i) {
			return $i->name;
		}, static::getColumns()));
	}

	static function insert($properties = array()) {
		static::getDB()->insert(static::getTable(), $properties, $id);

		return static::get($id);
	}

	public function update($property, $value) {
		if (property_exists($this, $property)) {
			if ($this->$property !== $value) {
				$this->$property = $value;
				$this->__updated = TRUE;
			}

			return TRUE;
		}

		return FALSE;
	}

	public function save() {
		if ($this->__updated) {

			$pk = static::getIDColumnName();
			$columns = static::getColumnNames();
			$properties = array();

			foreach (get_object_vars($this) as $property => $value) {
				if (in_array($property, $columns) && $property != $pk) {
					$properties[$property] = $value;
				}
			}

			if ($properties) {
				static::getDB()->update(static::getTable(), $properties, array(
				static::getIDColumnName() => $this->getID(),
				));
			}

			$this->__updated = FALSE;
		}

		return TRUE;
	}

	public function delete() {
		return static::getDB()->delete(static::getTable(), array(
			$this->getIDColumnName() => $this->getID(),
		));
	}

	static function getIDColumnName() {
		foreach (static::getDB()->query(" SHOW COLUMNS FROM " . static::getTable())->fetch_all() as $row) {
			if (isset($row['Key']) && $row['Key'] == 'PRI') {
				return $row['Field'];
			}
		}

		return FALSE;
	}

	static function getBy($properties = array(), $params = array()) {
		$sql = " SELECT SQL_CALC_FOUND_ROWS * FROM " . static::getTable() . " WHERE ( 1 ) ";

		foreach ((array) $properties as $property => $value) {
			$sql .= " AND ( " . $property . " = :" . $property . " ) ";
		}

		foreach ((array) $params as $param) {
			var_dump($param);
		}

		/*
		if (isset($params[\Katu\DB\Result::ORDERBY])) {
			$sql .= " ORDER BY " . $params[\Katu\DB\Result::ORDERBY];
		}

		if (isset($params[\Katu\DB\Result::PAGE])) {
			$sql .= " LIMIT " . $params[\Katu\DB\Result::PAGE]->getLimit();
		}
		*/

		return new DB\Result(static::getDB()->query($sql, $properties), static::getClass());
	}

	static function getOneBy() {
		return call_user_func_array(array('static', 'getBy'), func_get_args())->getOne();
	}

	static function getAll($params = array()) {
		return static::getBy(array(), $params);
	}

	static function get($primaryKey) {
		return static::getOneBy(array(static::getIDColumnName() => $primaryKey));
	}

	static function getOneOrCreate($getBy, $insert = array()) {
		$object = static::getOneBy($getBy);
		if (!$object) {
			$properties = array_merge($getBy, $insert);
			$object = self::insert($properties);
		}

		return $object;
	}

	static function getByQuery($sql) {
		return \Katu\DB\Result::get(static::getDB()->query($sql), static::getClass());
	}

	public function getID() {
		return $this->{static::getIDColumnName()};
	}

	static function getOrCreate() {
		$object = static::getByProperties(func_get_arg(0))->getOne();
		if (!$object) {
			$callable = array(get_called_class(), 'create');
			$args = array_slice(func_get_args(), 1);
			$object = call_user_func_array($callable, $args);
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

	static function getIDProperties() {
		return array_values(array_filter(array_map(function($i) {
			return preg_match('#^(?<property>[a-z_]+)_id$#', $i) ? $i : NULL;
		}, static::getColumnNames())));
	}

	public function getBoundObject($model) {
		$ns_model = '\\App\\Models\\' . $model;
		if (!class_exists($ns_model)) {
			return FALSE;
		}

		foreach (static::getIDProperties() as $property) {
			$_model = '\\App\\Models\\' . implode(array_map('ucfirst', explode('_', substr($property, 0, -3))));
			if ($_model && $ns_model == $_model) {
				$object = $_model::get($this->{$property});
				if ($object) {
					return $object;
				}
			}
		}

		return FALSE;
	}

	static function getPropertyName($property) {
		$properties = array_merge(array_keys(get_class_vars(get_called_class())), static::getColumnNames());

		foreach ($properties as $_property) {
			if (strtolower($_property) === strtolower($property)) {
				return $_property;
			}
		}

		return FALSE;
	}

	static function getColumnUniqueID($column) {
		$columns = static::getColumns();
		if (!$columns[$column]->length) {
			throw new Exception("Unable to get column length.");
		}

		do {

			$string = \Katu\Utils\Random::getIDString($columns[$column]->length);

		} while (static::getByProperty($column, $string)->getOne());

		return $string;
	}

}
