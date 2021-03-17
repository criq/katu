<?php

namespace Katu\Models\Presets;

class Role extends \Katu\Models\Model
{
	const TABLE = 'roles';

	public static function getRolePermissionClass() : \ReflectionClass
	{
		return new \ReflectionClass('App\Models\RolePermission');
	}

	public static function getUserRoleClass() : \ReflectionClass
	{
		return new \ReflectionClass('App\Models\UserRole');
	}

	public static function create($name)
	{
		if (!static::checkCrudParams($name)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid arguments.");
		}

		return static::insert([
			'timeCreated' => new \Katu\Tools\DateTime\DateTime,
			'name' => trim($name),
		]);
	}

	public static function make($name)
	{
		return static::upsert([
			'name' => $name,
		]);
	}

	public function delete()
	{
		foreach (static::getRolePermissionClass()->getName()::getBy([
			'roleId' => $this->getId(),
		]) as $rolePermission) {
			$rolePermission->delete();
		}

		foreach (static::getUserRoleClass()->getName()::getBy([
			'roleId' => $this->getId(),
		]) as $userRole) {
			$userRole->delete();
		}

		return parent::delete();
	}

	public static function checkCrudParams($name)
	{
		if (!static::checkName($name)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid name."))
				->addErrorName('name')
				;
		}

		return true;
	}

	public static function checkName($name, $object = null)
	{
		if (!trim($name)) {
			throw (new \Katu\Exceptions\InputErrorException("Missing name."))
				->addErrorName('name')
				;
		}

		// Look for another role with this name.
		$getBy['name'] = trim($name);
		if ($object) {
			$getBy[] = new \Sexy\CmpNotEq(static::getColumn('id'), $object->getId());
		}

		if (static::getBy($getBy)->getTotal()) {
			throw (new \Katu\Exceptions\InputErrorException("Another role with this name already exists."))
				->addErrorName('name')
				;
		}

		return true;
	}

	public function setName($name) : Role
	{
		if (!static::checkName($name, $this)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid name."))
				->addErrorName('name')
				;
		}

		$this->update('name', trim($name));

		return $this;
	}

	public function getName() : string
	{
		return $this->name;
	}

	public function addPermission($permission)
	{
		return static::getRolePermissionClass()->getName()::make($this, $permission);
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
		return static::getRolePermissionClass()->getName()::getBy([
			'roleId' => $this->getId(),
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
		return (bool)(static::getRolePermissionClass()->getName()::getOneBy([
			'roleId' => $this->getId(),
			'permission' => trim($permission),
		]));
	}

	public function deleteAllPermissions()
	{
		foreach (static::getRolePermissionClass()->getName()::getBy([
			'roleId' => $this->getId(),
		]) as $rolePermission) {
			$rolePermission->delete();
		}

		return true;
	}

	/****************************************************************************
	 * Permissions.
	 */
	public function userCanEdit($user) : bool
	{
		if (!$user) {
			return false;
		}

		return $user->hasPermission('roles.edit');
	}

	public function userCanEditPermissions($user) : bool
	{
		if (!$user) {
			return false;
		}

		return $user->hasPermission('roles.editPermissions');
	}

	public function userCanDelete($user) : bool
	{
		if (!$user) {
			return false;
		}

		return $user->hasPermission('roles.delete');
	}
}
