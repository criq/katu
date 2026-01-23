<?php

namespace Katu\Models\Presets;

use Katu\Tools\Users\RolePermissionInterface;

/**
 * @deprecated Use RolePermissionInterface instead. This class is kept for backward compatibility.
 */
abstract class RolePermission extends \Katu\Models\Model implements RolePermissionInterface
{
	const TABLE = "role_permissions";

	public static function create(RoleInterface $role, string $permission) : RolePermissionInterface
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

	public static function make(RoleInterface $role, string $permission) : RolePermissionInterface
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

	public function getRole(): RoleInterface
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\Role::class);

		return $class::get($this->roleId);
	}

	public static function isValidPermission($permission) : bool
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\UserPermission::class);

		return in_array($permission, $class::getAvailable());
	}
}
