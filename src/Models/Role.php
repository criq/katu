<?php

namespace Katu\Models;

class Role extends \Katu\Model {

	const TABLE = 'roles';

	static function create($name) {
		if (!self::checkCrudParams($name)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid arguments.");
		}

		return self::insert(array(
			'timeCreated' => (string) (\Katu\Utils\DateTime::get()->getDBDatetimeFormat()),
			'name'        => (string) (trim($name)),
		));
	}

	static function checkCrudParams($name) {
		if (!self::checkName($name)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid name.", 'name');
		}

		return TRUE;
	}

	static function checkName($name, $object = NULL) {
		if (!trim($name)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Missing name.", 'name');
		}

		// Look for another role with this name.
		$getBy['name'] = trim($name);
		if ($object) {
			$getBy[] = new \Katu\Pdo\Expressions\CmpNotEq(static::getColumn('id'), $object->id);
		}

		if (static::getBy($getBy)->getTotal()) {
			throw new \Katu\Exceptions\ArgumentErrorException("Another role with this name already exists.", 'name');
		}

		return TRUE;
	}

	public function setName($name) {
		if (!static::checkName($name, $this)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid name.", 'name');
		}

		$this->update('name', trim($name));

		return TRUE;
	}

}
