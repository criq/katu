<?php

namespace Katu\Models;

class UserRole extends \Katu\Model {

	const TABLE = 'user_roles';

	static function create($user, $role) {
		if (!static::checkCrudParams($user, $role)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid arguments.");
		}

		return static::insert(array(
			'timeCreated' => (string) (\Katu\Utils\DateTime::get()->getDbDateTimeFormat()),
			'userId'      => (int)    ($user->getId()),
			'roleId'      => (int)    ($role->getId()),
		));
	}

	static function make($user, $role) {
		return static::getOneOrCreateWithList(array(
			'userId' => (int) ($user->getId()),
			'roleId' => (int) ($role->getId()),
		), $user, $role);
	}

	static function checkCrudParams($user, $role) {
		if (!$user || !($user instanceof User)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid user.", 'user');
		}
		if (!$role || !($role instanceof Role)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid role.", 'role');
		}

		return true;
	}

}
