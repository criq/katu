<?php

namespace Katu\Models;

use \Katu\Exception;

class UserPermission extends \Katu\Model {

	const TABLE = 'user_permissions';

	static function create($user, $permission) {
		if (!self::checkCrudParams($user, $permission)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid arguments.");
		}

		return self::insert(array(
			'timeCreated' => (string) (\Katu\Utils\DateTime::get()->getDBDatetimeFormat()),
			'userId'      => (int)    ($user->id),
			'permission'  => (string) (trim($permission)),
		));
	}

	static function make($user, $permission) {
		if (!self::checkCrudParams($user, $permission)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid arguments.");
		}

		return self::getOneOrCreateWithList(array(
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

	static function isValidPermission($permission) {
		return TRUE;
	}

}
