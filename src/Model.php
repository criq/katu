<?php

namespace Jabli;

class Model {

	private $updated = FALSE;

	public $id;
	public $time_created;

	static function getDB() {
		return DB\Connection::getInstance();
	}

	static function getClass() {
		return get_called_class();
	}

	static function getTable() {
		$class = self::getClass();

		return $class::TABLE;
	}

	static function getColumns() {
		$columns = array();

		foreach (self::getDB()->query(" SHOW COLUMNS FROM " . self::getTable())->fetch_all() as $row) {
			$columns[] = $row['Field'];
		}

		return $columns;
	}

	static function insert($properties = array()) {
		if (in_array('time_created', self::getColumns())) {
			$properties['time_created'] = \Jabli\Utils\Datetime::get()->getDBDatetimeFormat();
		}

		self::getDB()->insert(self::getTable(), $properties, $id);

		return self::getByPK($id);
	}

	public function update($property, $value) {
		if (property_exists($this, $property)) {
			if ($this->$property !== $value) {
				$this->$property = $value;
				$this->updated   = TRUE;
			}

			return TRUE;
		}

		return FALSE;
	}

	public function save() {
		if ($this->updated) {

			$pk = self::getPKName();
			$columns = self::getColumns();
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

			$this->updated = FALSE;
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

	static function getAll() {
		return self::getByProperties();
	}

	static function getByProperties($properties = array(), $params = array()) {
		$sql = " SELECT SQL_CALC_FOUND_ROWS * FROM " . self::getTable() . " WHERE ( 1 ) ";

		foreach ($properties as $property => $value) {
			$sql .= " AND ( " . $property . " = :" . $property . " ) ";
		}

		if (isset($params[\Jabli\DB\Result::ORDERBY])) {
			$sql .= " ORDER BY " . $params[\Jabli\DB\Result::ORDERBY];
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

	public function __call($name, $args) {
		// Setter.
		if (preg_match('#^set(?<property>[a-z]+)$#i', $name, $match) && count($args) == 1) {
			$property = $match['property'];
			$value    = $args[0];

			if ($this->update($property, $value)) {
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

	static function getIDProperties() {
		return array_values(array_filter(array_map(function($i) {
			return preg_match('#^(?<property>[a-z_]+)_id$#', $i) ? $i : NULL;
		}, self::getColumns())));
	}

	public function getBoundObject($model) {
		if (!class_exists('\\App\\Models\\' . $model)) {
			return FALSE;
		}

		foreach (self::getIDProperties() as $property) {
			$_model = '\\App\\Models\\' . implode(array_map('ucfirst', explode('_', substr($property, 0, -3))));
			if ($_model) {
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

}
