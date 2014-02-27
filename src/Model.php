<?php

namespace Jabli\Aids;

class Model {

	private $updated = FALSE;

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

	static function insert($properties) {
		self::getDB()->insert(self::getTable(), $properties, $id);

		return self::getByPK($id)->getOne();
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
					self::getPKName() => $this->ID,
				));
			}

			$this->updated = FALSE;
		}

		return TRUE;
	}

	static function getPKName() {
		foreach (self::getDB()->query(" SHOW COLUMNS FROM " . self::getTable())->fetch_all() as $row) {
			if (isset($row['Key']) && $row['Key'] == 'PRI') {
				return $row['Field'];
			}
		}

		return FALSE;
	}

	static function getByProperties($properties) {
		$sql = " SELECT SQL_CALC_FOUND_ROWS * FROM " . self::getTable() . " WHERE ( 1 ) ";

		foreach ($properties as $property => $value) {
			$sql .= " AND ( " . $property . " = :" . $property . " ) ";
		}

		return new DB\Result(self::getDB()->query($sql, $properties), get_called_class());
	}

	static function getByProperty($property, $value) {
		return self::getByProperties(array(
			$property => $value,
		));
	}

	static function getByPK($pk) {
		return self::getByProperty(self::getPKName(), $pk);
	}

	public function getPK() {
		return $this->{self::getPKName()};
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

		user_error('Undeclared class method.');
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
