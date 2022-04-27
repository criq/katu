<?php

namespace Katu\Models\Presets;

abstract class RolePermission extends \Katu\Models\Model
{
	const TABLE = "role_permissions";

	public static function create(Role $role, string $permission) : RolePermission
	{
		if (!static::isValidPermission($permission)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid permission."))
				->addErrorName("permission")
				;
		}

		return static::insert([
			"timeCreated" => new \Katu\Tools\Calendar\Time,
			"roleId" => $role->getId(),
			"permission" => trim($permission),
		]);
	}

	public static function make(Role $role, string $permission) : RolePermission
	{
		if (!static::isValidPermission($permission)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid permission."))
				->addErrorName("permission")
				;
		}

		return static::upsert([
			"roleId" => $role->getId(),
			"permission" => trim($permission),
		], [
			"timeCreated" => new \Katu\Tools\Calendar\Time,
		]);
	}

	public static function isValidPermission($permission) : bool
	{
		return in_array($permission, \App\App::getUserPermissionModelClass()->getName()::getAvailable());
	}
}
