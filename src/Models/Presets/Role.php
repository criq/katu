<?php

namespace Katu\Models\Presets;

abstract class Role extends \Katu\Models\Model
{
	const TABLE = "roles";

	public static function create($name)
	{
		if (!static::sanitizeName($name)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid arguments.");
		}

		return static::insert([
			"timeCreated" => new \Katu\Tools\Calendar\Time,
			"name" => trim($name),
		]);
	}

	public static function make($name)
	{
		return static::upsert([
			"name" => $name,
		]);
	}

	public function delete(): bool
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\RolePermission::class);
		foreach ($class::getBy([
			"roleId" => $this->getId(),
		]) as $rolePermission) {
			$rolePermission->delete();
		}

		$class = \App\App::getContainer()->get(\Katu\Models\Presets\UserRole::class);
		foreach ($class::getBy([
			"roleId" => $this->getId(),
		]) as $userRole) {
			$userRole->delete();
		}

		return parent::delete();
	}

	public static function sanitizeName(string $value) : ?string
	{
		$value = trim($value);
		if (!strlen($value)) {
			throw new \Katu\Exceptions\InputErrorException("Missing name.");
		}

		return $value;
	}

	public function setName($name) : Role
	{
		if (!static::sanitizeName($name, $this)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid name."))
				->addErrorName("name")
				;
		}

		$this->name = trim($name);

		return $this;
	}

	public function getName() : string
	{
		return $this->name;
	}

	public function addPermission($permission)
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\RolePermission::class);

		return $class::make($this, $permission);
	}

	public function addPermissions($permissions)
	{
		foreach ((array)$permissions as $permission) {
			$this->addPermission($permission);
		}

		return true;
	}

	public function getRolePermissions()
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\RolePermission::class);

		return $class::getBy([
			"roleId" => $this->getId(),
		]);
	}

	public function getPermissions()
	{
		return array_map(function ($rolePermission) {
			return $rolePermission->permission;
		}, $this->getRolePermissions()->getItems());
	}

	public function hasPermission($permission) : bool
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\RolePermission::class);

		return (bool)($class::getOneBy([
			"roleId" => $this->getId(),
			"permission" => trim($permission),
		]));
	}

	public function deleteAllPermissions()
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\RolePermission::class);

		foreach ($class::getBy([
			"roleId" => $this->getId(),
		]) as $rolePermission) {
			$rolePermission->delete();
		}

		return true;
	}

	/****************************************************************************
	 * Permissions.
	 */
	public function userCanView($user) : bool
	{
		if (!$user) {
			return false;
		}

		return $user->hasPermission("roles.view");
	}

	public function userCanEdit($user) : bool
	{
		if (!$user) {
			return false;
		}

		return $user->hasPermission("roles.edit");
	}

	public function userCanEditPermissions($user) : bool
	{
		if (!$user) {
			return false;
		}

		return $user->hasPermission("roles.editPermissions");
	}

	public function userCanDelete($user) : bool
	{
		if (!$user) {
			return false;
		}

		return $user->hasPermission("roles.delete");
	}
}
