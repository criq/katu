<?php

namespace Katu\Models;

class UserPermission extends \Katu\Model {

	const TABLE = 'user_permissions';

	static function create($user, $permission) {
		if (!static::checkCrudParams($user, $permission)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid arguments.");
		}

		return static::insert(array(
			'timeCreated' => (string) (\Katu\Utils\DateTime::get()->getDbDatetimeFormat()),
			'userId'      => (int)    ($user->id),
			'permission'  => (string) (trim($permission)),
		));
	}

	static function make($user, $permission) {
		if (!static::checkCrudParams($user, $permission)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid arguments.");
		}

		return static::getOneOrCreateWithList(array(
			'userId'     => (int)    ($user->id),
			'permission' => (string) (trim($permission)),
		), $user, $permission);
	}

	static function checkCrudParams($user, $permission) {
		if (!$user || !($user instanceof User)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid user.", 'user');
		}
		if (!trim($permission)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Missing permission.", 'permission');
		}
		if (!static::isValidPermission($permission)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid permission.", 'permission');
		}

		return TRUE;
	}

	static function getAvailable() {
		$permissionFilePath = BASE_DIR . '/app/Config/userPermissions.yaml';
		if (!file_exists($permissionFilePath)) {
			throw new \Katu\Exceptions\ErrorException("Permission file doesn't exist.");
		}

		$permissions = array_unique(array_filter(array_map('trim', \Katu\Utils\YAML::decode($permissionFilePath))));
		if (!$permissions) {
			throw new \Katu\Exceptions\ErrorException("No permissions found.");
		}

		return $permissions;
	}

	static function isValidPermission($permission) {
		return in_array($permission, static::getAvailable());
	}

}
