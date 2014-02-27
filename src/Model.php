<?php

namespace Jabli\Aids;

class Model {

	static function getDB() {
		return DB::getInstance();
	}

	static function getClass() {
		return get_called_class();
	}

	static function getTable() {
		$class = self::getClass();

		return $class::TABLE;
	}

	static function insert($properties) {
		self::getDB()->insert(self::getTable(), $properties, $id);

		return self::getByPK($id);
	}

	static function getPrimaryKey() {
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

		return self::getDB()->query($sql, $properties);
	}

	static function getByProperty($property, $value) {
		return self::getByProperties(array(
			$property => $value,
		));
	}

	static function getByPK($pk) {
		return self::getByProperty(self::getPrimaryKey(), $pk);
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

}
