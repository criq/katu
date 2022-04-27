<?php

namespace Katu\Models\Presets;

abstract class UserRole extends \Katu\Models\Model
{
	const TABLE = "user_roles";

	public static function create(User $user, Role $role): UserRole
	{
		return static::insert([
			"timeCreated" => new \Katu\Tools\Calendar\Time,
			"userId" => $user->getId(),
			"roleId" => $role->getId(),
		]);
	}

	public static function make(User $user, Role $role): UserRole
	{
		return static::upsert([
			"userId" => $user->getId(),
			"roleId" => $role->getId(),
		], [
			"timeCreated" => new \Katu\Tools\Calendar\Time,
		]);
	}
}
