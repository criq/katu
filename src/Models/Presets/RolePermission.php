<?php

namespace Katu\Models\Presets;

class RolePermission extends \Katu\Models\Model
{
	const TABLE = 'role_permissions';

	public static function getUserPermissionClass() : \ReflectionClass
	{
		return new \ReflectionClass('Katu\Models\Presets\UserPermission');
	}

	public static function create(Role $role, string $permission) : RolePermission
	{
		if (!static::isValidPermission($permission)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid permission."))
				->addErrorName('permission')
				;
		}

		return static::insert([
			'timeCreated' => new \Katu\Tools\DateTime\DateTime,
			'roleId' => $role->getId(),
			'permission' => trim($permission),
		]);
	}

	public static function make(Role $role, string $permission) : RolePermission
	{
		if (!static::isValidPermission($permission)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid permission."))
				->addErrorName('permission')
				;
		}

		return static::upsert([
			'roleId' => $role->getId(),
			'permission' => trim($permission),
		], [
			'timeCreated' => new \Katu\Tools\DateTime\DateTime,
		]);
	}

	public static function isValidPermission($permission) : bool
	{
		return in_array($permission, static::getUserPermissionClass()->getName()::getAvailable());
	}
}
