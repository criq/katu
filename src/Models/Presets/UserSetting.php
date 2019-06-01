<?php

namespace Katu\Models\Presets;

class UserSetting extends \Katu\Model {

	const TABLE = 'user_settings';

	static function create($user, $name) {
		return static::insert(array(
			'timeCreated' => (string) (\Katu\Tools\DateTime\DateTime::get()->getDbDateTimeFormat()),
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
