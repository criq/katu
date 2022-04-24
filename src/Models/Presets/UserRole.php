<?php

namespace Katu\Models\Presets;

class UserRole extends \Katu\Models\Model
{
	const TABLE = 'user_roles';

	public static function create(User $user, Role $role)
	{
		return static::insert([
			'timeCreated' => new \Katu\Tools\Calendar\Time,
			'userId' => $user->getId(),
			'roleId' => $role->getId(),
		]);
	}

	public static function make($user, $role)
	{
		return static::upsert([
			'userId' => $user->getId(),
			'roleId' => $role->getId(),
		], [
			'timeCreated' => new \Katu\Tools\Calendar\Time,
		]);
	}
}
