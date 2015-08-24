<?php

namespace Katu\Models;

use \Katu\Exception;

class UserSetting extends \Katu\Model {

	const DATABASE = 'app';
	const TABLE = 'user_settings';

	static function create($user, $name) {
		return static::insert(array(
			'timeCreated' => (string) (\Katu\Utils\DateTime::get()->getDbDateTimeFormat()),
			'userId'      => (int)    ($user->getId()),
			'name'        => (string) ($name),
		));
	}

	static function make($user, $name) {
		return static::getOneOrCreateWithList(array(
			'userId'      => (int)    ($user->getId()),
			'name'        => (string) ($name),
		), $user, $name);
	}

}
