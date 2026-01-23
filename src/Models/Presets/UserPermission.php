<?php

namespace Katu\Models\Presets;

use App\Config\UserPermissionConfig;
use Katu\Tools\Users\UserPermissionInterface;

/**
 * @deprecated Use UserPermissionInterface instead. This class is kept for backward compatibility.
 */
abstract class UserPermission extends \Katu\Models\Model implements UserPermissionInterface
{
	const TABLE = "user_permissions";

	public static function create(UserInterface $user, string $permission): UserPermissionInterface
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

	public static function make(UserInterface $user, string $permission): UserPermissionInterface
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

	public function getUser(): UserInterface
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\User::class);

		return $class::get($this->userId);
	}

	public static function isValidPermission(string $permission): bool
	{
		return in_array($permission, static::getAvailable());
	}
}
