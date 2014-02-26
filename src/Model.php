<?php

namespace Jabli\Aids;

class Model {

	static function getTable() {
		$class = get_called_class();

		return $class::TABLE;
	}

	static function getPrimaryKey() {
		$db = DB::getInstance();

		foreach ($db->query(" SHOW COLUMNS FROM " . self::getTable())->fetch_all() as $row) {
			if (isset($row['Key']) && $row['Key'] == 'PRI') {
				return $row['Field'];
			}
		}

		return FALSE;
	}

	static function getByPK($pk) {
		$db = DB::getInstance();
		$class = get_called_class();

		$res = $db->query(" SELECT * FROM " . self::getTable() . " WHERE ( " . self::getPrimaryKey() . " = :pk ) ", array(
			'pk' => $pk,
		))->fetch_one();

		if (!$res) {
			return FALSE;
		}

		return self::getFromAssoc($res);
	}

	static function getFromAssoc($array) {
		$class = get_called_class();

		if (!$array) {
			return FALSE;
		}

		$object = new $class;

		foreach ($array as $key => $value) {
			if (property_exists($object, $key)) {
				$object->$key = $value;
			}
		}

		return $object;
	}

}
