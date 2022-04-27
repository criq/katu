<?php

namespace Katu\Models\Presets;

class Role extends \Katu\Models\Model
{
	const DATABASE = "app";
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
		foreach (\App\App::getRolePermissionModelClass()->getName()::getBy([
			"roleId" => $this->getId(),
		]) as $rolePermission) {
			$rolePermission->delete();
		}

		foreach (\App\App::getUserRoleModelClass()->getName()::getBy([
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
		return \App\App::getRolePermissionModelClass()->getName()::make($this, $permission);
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
		return \App\App::getRolePermissionModelClass()->getName()::getBy([
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
		return (bool)(\App\App::getRolePermissionModelClass()->getName()::getOneBy([
			"roleId" => $this->getId(),
			"permission" => trim($permission),
		]));
	}

	public function deleteAllPermissions()
	{
		foreach (\App\App::getRolePermissionModelClass()->getName()::getBy([
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
