<?php

namespace Katu\Models;

class RolePermission extends \Katu\Model {

	const TABLE = 'role_permissions';

	static function create($role, $permission) {
		if (!static::checkCrudParams($role, $permission)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid arguments.");
		}

		return static::insert(array(
			'timeCreated' => (string) (\Katu\Utils\DateTime::get()->getDbDateTimeFormat()),
			'roleId'      => (int)    ($role->id),
			'permission'  => (string) (trim($permission)),
		));
	}

	static function make($role, $permission) {
		if (!static::checkCrudParams($role, $permission)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid arguments.");
		}

		return static::getOneOrCreateWithList(array(
			'roleId'     => (int)    ($role->id),
			'permission' => (string) (trim($permission)),
		), $role, $permission);
	}

	static function checkCrudParams($role, $permission) {
		if (!$role || !($role instanceof Role)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid role.", 'role');
		}
		if (!trim($permission)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Missing permission.", 'permission');
		}
		if (!static::isValidPermission($permission)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid permission.", 'permission');
		}

		return true;
	}

	static function isValidPermission($permission) {
		return in_array($permission, \App\Models\UserPermission::getAvailable());
	}

}
