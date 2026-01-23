<?php

namespace Katu\Models\Presets;

use Katu\Tools\Users\UserRoleInterface;

/**
 * @deprecated Use UserRoleInterface instead. This class is kept for backward compatibility.
 */
abstract class UserRole extends \Katu\Models\Model implements UserRoleInterface
{
	const TABLE = "user_roles";

	public static function create(UserInterface $user, RoleInterface $role): UserRoleInterface
	{
		return static::insert([
			"timeCreated" => new \Katu\Tools\Calendar\Time,
			"userId" => $user->getId(),
			"roleId" => $role->getId(),
		]);
	}

	public function getUser(): UserInterface
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\User::class);

		return $class::get($this->userId);
	}

	public function getRole(): RoleInterface
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\Role::class);

		return $class::get($this->roleId);
	}

	public static function make(UserInterface $user, RoleInterface $role): UserRoleInterface
	{
		return static::upsert([
			"userId" => $user->getId(),
			"roleId" => $role->getId(),
		], [
			"timeCreated" => new \Katu\Tools\Calendar\Time,
		]);
	}
}
