<?php

namespace Katu\Models;

class Role extends \Katu\Model {

	const DATABASE = 'app';
	const TABLE = 'roles';

	static function create($name) {
		if (!static::checkCrudParams($name)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid arguments.");
		}

		return static::insert(array(
			'timeCreated' => (string) (\Katu\Utils\DateTime::get()->getDbDateTimeFormat()),
			'name'        => (string) (trim($name)),
		));
	}

	static function make($name) {
		return static::getOneOrCreateWithList(array(
			'name' => $name,
		), $name);
	}

	public function delete() {
		foreach (\App\Models\RolePermission::getBy(array(
			'roleId' => $this->getId(),
		)) as $rolePermission) {
			$rolePermission->delete();
		}

		foreach (\App\Models\UserRole::getBy(array(
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
		return \App\Models\RolePermission::make($this, $permission);
	}

	public function addPermissions($permissions) {
		foreach ((array) $permissions as $permission) {
			$this->addPermission($permission);
		}

		return true;
	}

	public function hasPermission($permission) {
		return (bool) \App\Models\RolePermission::getOneBy(array(
			'roleId'     => (int)    ($this->getId()),
			'permission' => (string) (trim($permission)),
		));
	}

	public function deleteAllPermissions() {
		foreach (\App\Models\RolePermission::getBy(array(
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
