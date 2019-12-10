<?php

namespace Katu\Models\Presets;

class UserPermission extends \Katu\Models\Model {

	const TABLE = 'user_permissions';

	static function create($user, $permission) {
		if (!static::checkCrudParams($user, $permission)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid arguments.");
		}

		return static::insert(array(
			'timeCreated' => (string) (\Katu\Tools\DateTime\DateTime::get()->getDbDateTimeFormat()),
			'userId'      => (int)    ($user->getId()),
			'permission'  => (string) (trim($permission)),
		));
	}

	static function make($user, $permission) {
		return static::getOneOrCreateWithList(array(
			'userId'     => (int)    ($user->getId()),
			'permission' => (string) (trim($permission)),
		), $user, $permission);
	}

	static function checkCrudParams($user, $permission) {
		if (!$user || !($user instanceof User)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid user."))
				->addErrorName('user')
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

	static function getAvailable() {
		$permissionFilePath = \Katu\App::getBaseDir() . '/app/Config/userPermissions.yaml';
		if (!file_exists($permissionFilePath)) {
			throw new \Katu\Exceptions\ErrorException("Permission file doesn't exist.");
		}

		$permissions = array_unique(array_filter(array_map('trim', \Katu\Files\Formats\YAML::decode($permissionFilePath))));
		if (!$permissions) {
			throw new \Katu\Exceptions\ErrorException("No permissions found.");
		}

		natsort($permissions);

		return $permissions;
	}

	static function isValidPermission($permission) {
		return in_array($permission, static::getAvailable());
	}

}
