<?php

namespace Katu\Models\Presets;

class UserRole extends \Katu\Model {

	const TABLE = 'user_roles';

	static function create($user, $role) {
		if (!static::checkCrudParams($user, $role)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid arguments.");
		}

		return static::insert(array(
			'timeCreated' => (string) (\Katu\Tools\DateTime\DateTime::get()->getDbDateTimeFormat()),
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
			throw (new \Katu\Exceptions\InputErrorException("Invalid user."))
				->addErrorName('user')
				;
		}
		if (!$role || !($role instanceof Role)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid role."))
				->addErrorName('role')
				;
		}

		return true;
	}

}
