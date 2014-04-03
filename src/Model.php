<?php

namespace Jabli;

class Model {

	public $id;
	public $time_created;

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

		foreach (self::getDB()->query(" SHOW COLUMNS FROM " . self::getTable())->fetch_all() as $row) {
			$columns[$row['Field']] = new \Jabli\DB\Column($row);
		}

		return $columns;
	}

	static function getColumn($name) {
		$columns = self::getColumns();
		if (!isset($columns[$name])) {
			throw new Exception("Invalid column " . $name . ".");
		}

		return $columns[$name];
	}

	static function getColumnNames() {
		return array_values(array_map(function($i) {
			return $i->name;
		}, self::getColumns()));
	}

	static function insert($properties = array()) {
		if (in_array('time_created', self::getColumnNames())) {
			$properties['time_created'] = \Jabli\Utils\Datetime::get()->getDBDatetimeFormat();
		}

		self::getDB()->insert(self::getTable(), $properties, $id);

		return self::getByPK($id);
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

			$pk = self::getPKName();
			$columns = self::getColumnNames();
			$properties = array();

			foreach (get_object_vars($this) as $property => $value) {
				if (in_array($property, $columns) && $property != $pk) {
					$properties[$property] = $value;
				}
			}

			if ($properties) {
				self::getDB()->update(self::getTable(), $properties, array(
				self::getPKName() => $this->getPK(),
				));
			}

			$this->__updated = FALSE;
		}

		return TRUE;
	}

	public function delete() {
		return self::getDB()->delete(self::getTable(), array(
			$this->getPKName() => $this->getPK(),
		));
	}

	static function getPKName() {
		foreach (self::getDB()->query(" SHOW COLUMNS FROM " . self::getTable())->fetch_all() as $row) {
			if (isset($row['Key']) && $row['Key'] == 'PRI') {
				return $row['Field'];
			}
		}

		return FALSE;
	}

	static function getAll($params = array()) {
		return self::getByProperties(array(), $params);
	}

	static function getByProperties($properties = array(), $params = array()) {
		$sql = " SELECT SQL_CALC_FOUND_ROWS * FROM " . self::getTable() . " WHERE ( 1 ) ";

		foreach ($properties as $property => $value) {
			$sql .= " AND ( " . $property . " = :" . $property . " ) ";
		}

		if (isset($params[\Jabli\DB\Result::ORDERBY])) {
			$sql .= " ORDER BY " . $params[\Jabli\DB\Result::ORDERBY];
		}

		if (isset($params[\Jabli\DB\Result::PAGE])) {
			$sql .= " LIMIT " . $params[\Jabli\DB\Result::PAGE]->getLimit();
		}

		return new DB\Result(self::getDB()->query($sql, $properties), get_called_class());
	}

	static function getByProperty($property, $value, $params = array()) {
		return self::getByProperties(array($property => $value), $params);
	}

	static function getByPK($pk) {
		return self::getByProperty(self::getPKName(), $pk)->getOne();
	}

	public function getPK() {
		return $this->{self::getPKName()};
	}

	static function getOrCreate() {
		$object = self::getByProperties(func_get_arg(0))->getOne();
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

		$class = self::getClass();
		$object = new $class;

		foreach ($array as $key => $value) {
			if (property_exists($object, $key)) {
				$object->$key = $value;
			}
		}

		return $object;
	}

	static function getIDProperties() {
		return array_values(array_filter(array_map(function($i) {
			return preg_match('#^(?<property>[a-z_]+)_id$#', $i) ? $i : NULL;
		}, self::getColumnNames())));
	}

	public function getBoundObject($model) {
		$ns_model = '\\App\\Models\\' . $model;
		if (!class_exists($ns_model)) {
			return FALSE;
		}

		foreach (self::getIDProperties() as $property) {
			$_model = '\\App\\Models\\' . implode(array_map('ucfirst', explode('_', substr($property, 0, -3))));
			if ($_model && $ns_model == $_model) {
				$object = $_model::getByPK($this->{$property});
				if ($object) {
					return $object;
				}
			}
		}

		return FALSE;
	}

	static function getPropertyName($property) {
		foreach (get_class_vars(get_called_class()) as $var => $value) {
			if (strtolower($property) === strtolower($var)) {
				return $var;
			}
		}

		return FALSE;
	}

	static function getColumnUniqueID($column) {
		$columns = self::getColumns();
		if (!isset($columns[$column]['length'])) {
			throw new Exception("Unable to get column length.");
		}

		do {

			$string = \Jabli\Utils\Random::getIDString($columns[$column]['length']);

		} while (self::getByProperty($column, $string)->getOne());

		return $string;
	}

}
