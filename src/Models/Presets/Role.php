<?php

namespace Katu\Models\Presets;

class Role extends \Katu\Models\Model {

	const TABLE = 'roles';

	static function getRolePermissionClass() {
		return '\\App\\Models\\RolePermission';
	}

	static function getUserRoleClass() {
		return '\\App\\Models\\UserRole';
	}

	static function create($name) {
		if (!static::checkCrudParams($name)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid arguments.");
		}

		return static::insert(array(
			'timeCreated' => (string) (\Katu\Tools\DateTime\DateTime::get()->getDbDateTimeFormat()),
			'name'        => (string) (trim($name)),
		));
	}

	static function make($name) {
		return static::getOneOrCreateWithList(array(
			'name' => $name,
		), $name);
	}

	public function delete() {
		$rolePermissionClass = static::getRolePermissionClass();
		$userRoleClass = static::getUserRoleClass();

		foreach ($rolePermissionClass::getBy(array(
			'roleId' => $this->getId(),
		)) as $rolePermission) {
			$rolePermission->delete();
		}

		foreach ($userRoleClass::getBy(array(
			'roleId' => $this->getId(),
		)) as $userRole) {
			$userRole->delete();
		}

		return parent::delete();
	}

	static function checkCrudParams($name) {
		if (!static::checkName($name)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid name."))
				->addErrorName('name')
				;
		}

		return true;
	}

	static function checkName($name, $object = null) {
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

	public function setName($name) {
		if (!static::checkName($name, $this)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid name."))
				->addErrorName('name')
				;
		}

		$this->update('name', trim($name));

		return true;
	}

	public function addPermission($permission) {
		$rolePermissionClass = static::getRolePermissionClass();

		return $rolePermissionClass::make($this, $permission);
	}

	public function addPermissions($permissions) {
		foreach ((array)$permissions as $permission) {
			$this->addPermission($permission);
		}

		return true;
	}

	public function getRolePermissions() {
		$rolePermissionClass = static::getRolePermissionClass();

		return $rolePermissionClass::getBy([
			'roleId' => $this->getId(),
		]);
	}

	public function getPermissions() {
		return array_map(function($rolePermission) {
			return $rolePermission->permission;
		}, $this->getRolePermissions()->getObjects());
	}

	public function hasPermission($permission) {
		$rolePermissionClass = static::getRolePermissionClass();

		return (bool) $rolePermissionClass::getOneBy(array(
			'roleId'     => (int)    ($this->getId()),
			'permission' => (string) (trim($permission)),
		));
	}

	public function deleteAllPermissions() {
		$rolePermissionClass = static::getRolePermissionClass();

		foreach ($rolePermissionClass::getBy(array(
			'roleId' => $this->getId(),
		)) as $rolePermission) {
			$rolePermission->delete();
		}

		return true;
	}

	public function userCanEdit($user) {
		if (!$user) {
			return false;
		}

		return $user->hasPermission('roles.edit');
	}

	public function userCanEditPermissions($user) {
		if (!$user) {
			return false;
		}

		return $user->hasPermission('roles.editPermissions');
	}

	public function userCanDelete($user) {
		if (!$user) {
			return false;
		}

		return $user->hasPermission('roles.delete');
	}

}
