<?php

namespace Katu\Models;

use \Katu\Exception;

class UserAC extends \Katu\Model {

	const TABLE = 'user_ac';

	static function create($user, $ac) {
		if (!self::checkCRUDParams($user, $ac)) {
			throw new \Exception("Invalid params.");
		}

		return self::insert(array(
			'timeCreated' => (string) (\Katu\Utils\DateTime::get()->getDBDatetimeFormat()),
			'userId'      => (int)    ($user->id),
			'ac'          => (string) (trim($ac)),
		));
	}

	static function make($user, $ac) {
		return self::getOneOrCreateWithList(array(
			'userId' => (int)    ($user->id),
			'ac'     => (string) (trim($ac)),
		), $user, $ac);
	}

	static function checkCRUDParams($user, $ac) {
		if (!$user || !($user instanceof User)) {
			throw new \Exception("Invalid user.");
		}
		if (!trim($ac)) {
			throw new \Exception("Invalid AC.");
		}

		return TRUE;
	}

}
