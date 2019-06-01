<?php

namespace Katu\Models\Presets;

class RolePermission extends \Katu\Models\Model {

	const TABLE = 'role_permissions';

	static function create($role, $permission) {
		if (!static::checkCrudParams($role, $permission)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid arguments.");
		}

		return static::insert(array(
			'timeCreated' => (string) (\Katu\Tools\DateTime\DateTime::get()->getDbDateTimeFormat()),
			'roleId'      => (int)    ($role->getId()),
			'permission'  => (string) (trim($permission)),
		));
	}

	static function make($role, $permission) {
		if (!static::checkCrudParams($role, $permission)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid arguments.");
		}

		return static::getOneOrCreateWithList(array(
			'roleId'     => (int)    ($role->getId()),
			'permission' => (string) (trim($permission)),
		), $role, $permission);
	}

	static function checkCrudParams($role, $permission) {
		if (!$role || !($role instanceof Role)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid role."))
				->addErrorName('role')
				;
		}
		if (!trim($permission)) {
			throw (new \Katu\Exceptions\InputErrorException("Missing permission."))
				->addErrorName('permission')
				;
		}
		if (!static::isValidPermission($permission)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid permission."))
				->addErrorName('permission')
				;
		}

		return true;
	}

	static function isValidPermission($permission) {
		$userPermissionClass = class_exists('\\App\\Models\\UserPermission') ? '\\App\\Models\\UserPermission' : '\\Katu\\Models\\UserPermission';

		return in_array($permission, $userPermissionClass::getAvailable());
	}

}
