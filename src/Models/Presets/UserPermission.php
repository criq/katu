<?php

namespace Katu\Models\Presets;

use App\Config\UserPermissionConfig;

abstract class UserPermission extends \Katu\Models\Model
{
	const TABLE = "user_permissions";

	public static function create(User $user, string $permission): UserPermission
	{
		if (!static::isValidPermission($permission)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid permission."))
				->addErrorName("permission")
				;
		}

		return static::insert([
			"timeCreated" => new \Katu\Tools\Calendar\Time,
			"userId" => $user->getId(),
			"permission" => trim($permission),
		]);
	}

	public static function make(User $user, string $permission): UserPermission
	{
		if (!static::isValidPermission($permission)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid permission."))
				->addErrorName("permission")
				;
		}

		return static::upsert([
			"userId" => $user->getId(),
			"permission" => trim($permission),
		], [
			"timeCreated" => new \Katu\Tools\Calendar\Time,
		]);
	}

	public static function getAvailable(): array
	{
		return (new UserPermissionConfig)->getPermissions();
	}

	public static function isValidPermission(string $permission): bool
	{
		return in_array($permission, static::getAvailable());
	}
}
