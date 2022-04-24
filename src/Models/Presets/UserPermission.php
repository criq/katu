<?php

namespace Katu\Models\Presets;

class UserPermission extends \Katu\Models\Model
{
	const TABLE = 'user_permissions';

	public static function create(User $user, string $permission)
	{
		if (!static::isValidPermission($permission)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid permission."))
				->addErrorName('permission')
				;
		}

		return static::insert([
			'timeCreated' => new \Katu\Tools\Calendar\Time,
			'userId' => $user->getId(),
			'permission' => trim($permission),
		]);
	}

	public static function make(User $user, string $permission)
	{
		if (!static::isValidPermission($permission)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid permission."))
				->addErrorName('permission')
				;
		}

		return static::upsert([
			'userId' => $user->getId(),
			'permission' => trim($permission),
		], [
			'timeCreated' => new \Katu\Tools\Calendar\Time,
		]);
	}

	public static function getAvailable()
	{
		$file = new \Katu\Files\File(\Katu\App::getBaseDir(), 'app', 'Config', ['userPermissions', 'yaml']);
		if (!$file->exists()) {
			throw new \Katu\Exceptions\ErrorException("Permission file doesn't exist.");
		}

		$permissions = array_unique(array_filter(array_map('trim', \Katu\Files\Formats\YAML::decode($file->get()))));
		if (!$permissions) {
			throw new \Katu\Exceptions\ErrorException("No permissions found.");
		}

		natsort($permissions);

		return $permissions;
	}

	public static function isValidPermission($permission)
	{
		return in_array($permission, static::getAvailable());
	}
}
