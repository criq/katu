<?php

namespace Jabli\Models;

use \Jabli\Exception;

class UserAC extends \Jabli\Model {

	const TABLE = 'user_ac';

	public $user_id;
	public $ac;

	static function create($user, $ac) {
		if (!self::checkCRUDParams($user, $ac)) {
			throw new Exception("Invalid params.");
		}

		return self::insert(array(
			'user_id' => (int)    ($user->id),
			'ac'      => (string) (trim($ac)),
		));
	}

	static function make($user, $ac) {
		return self::getOrCreate(array(
			'user_id' => (int)    ($user->id),
			'ac'      => (string) (trim($ac)),
		), $user, $ac);
	}

	static function checkCRUDParams($user, $ac) {
		if (!$user || !($user instanceof User)) {
			throw new Exception("Invalid user.");
		}
		if (!trim($ac)) {
			throw new Exception("Invalid AC.");
		}

		return TRUE;
	}

}
