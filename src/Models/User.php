<?php

namespace Katu\Models;

use \Katu\Exception;

class User extends \Katu\Model {

	const TABLE = 'users';

	static function create() {
		return static::insert(array(
			'timeCreated' => (string) (\Katu\Utils\DateTime::get()->getDBDatetimeFormat()),
		));
	}

	static function createWithEmailAddress($emailAddress) {
		if (!$emailAddress || !($emailAddress instanceof EmailAddress)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid e-mail address.", 'emailAddress');
		}

		// Look for another user with this e-mail address.
		if (static::getBy(array(
			'emailAddressId' => $emailAddress->id,
		))->getTotal()) {
			throw new \Katu\Exceptions\ArgumentErrorException("E-mail address is already in use.", 'emailAddress');
		}

		$object = parent::create();
		$object->setEmailAddress($emailAddress);
		$object->save();

		return $object;
	}

	static function getCurrent() {
		return static::get(\Katu\Session::get('katu.user.id'));
	}

	public function addUserService($serviceName, $serviceUserID) {
		return \App\Models\UserService::create($this, $serviceName, $serviceUserID);
	}

	public function getDefaultUserServiceByName($serviceName) {
		return \App\Models\UserService::getOneBy(array(
			'userID'      => (int)    ($this->id),
			'serviceName' => (string) ($serviceName),
		));
	}

	public function setEmailAddress($emailAddress) {
		if (!$emailAddress || !($emailAddress instanceof EmailAddress)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid e-mail address.", 'emailAddress');
		}

		// Look for another user with this e-mail address.
		if (static::getBy(array(
			'emailAddressId' => $emailAddress->id,
			new CmpNotEq(static::getColumn('id'), $this->id),
		))->getTotal()) {
			throw new \Katu\Exceptions\ArgumentErrorException("E-mail address is used by another user.", 'emailAddress');
		}

		$this->update('emailAddressId', $emailAddress->id);

		return TRUE;
	}

	public function setName($name) {
		$this->update('name', trim($name));

		return TRUE;
	}

	public function login() {
		return \Katu\Session::set('katu.user.id', (int) $this->id);
	}

	static function logout() {
		return \Katu\Session::reset('katu.user.id');
	}

	public function addRole($role) {
		return \App\Models\UserRole::make($this, $role);
	}

	public function addRolesByIds($roleIds) {
		$roles = array();

		foreach ((array) $roleIds as $roleId) {
			$role = \App\Models\Role::get($roleId);
			if (!$role) {
				throw new \Katu\Exceptions\InputErrorException("Invalid role ID.");
			}

			$roles[] = $role;
		}

		foreach ($roles as $role) {
			$this->addRole($role);
		}

		return TRUE;
	}

	public function hasRole($role) {
		return (bool) \App\Models\UserRole::getOneBy(array(
			'userId' => (int) ($this->id),
			'roleId' => (int) ($role->id),
		));
	}

	public function deleteAllRoles() {
		foreach (\App\Models\UserRole::getBy(array(
			'userId' => $this->id,
		)) as $userRole) {
			$userRole->delete();
		}

		return TRUE;
	}

	public function addUserPermission($permission) {
		return \App\Models\UserPermission::make($this, $permission);
	}

	public function addUserPermissions($permissions) {
		foreach ((array) $permissions as $permission) {
			$this->addUserPermission($permission);
		}

		return TRUE;
	}

	public function deleteAllUserPermissions() {
		foreach (\App\Models\UserPermission::getBy(array(
			'userId' => $this->id,
		)) as $userPermission) {
			$userPermission->delete();
		}

		return TRUE;
	}

	static function currentHasPermission($permission) {
		$user = static::getCurrent();
		if (!$user) {
			return FALSE;
		}

		return $user->hasPermission($permission);
	}

	public function getRolePermissions() {
		$sql = (new \Katu\Pdo\Expressions\Select(\App\Models\RolePermission::getColumn('permission')))
			->from(\App\Models\RolePermission::getTable())
			->join(\App\Models\UserRole::getColumn('roleId'), \App\Models\RolePermission::getColumn('roleId'))
			->where(\App\Models\UserRole::getColumn('userId'), $this->id)
			->groupBy(\App\Models\RolePermission::getColumn('permission'));

		return static::getPdo()->createQueryFromSql($sql)->getResult()->getColumnValues('permission');
	}

	public function getUserPermissions() {
		return \App\Models\UserPermission::getBy(array(
			'userId' => (int) ($this->id),
		))->getPropertyValues('permission');
	}

	public function getAllPermissions() {
		return array_unique(array_merge($this->getRolePermissions(), $this->getUserPermissions()));
	}

	public function hasPermission($permission) {
		return in_array($permission, $this->getAllPermissions());
	}

	public function hasRolePermission($permission) {
		return in_array($permission, $this->getRolePermissions());
	}

	public function hasUserPermission($permission) {
		return in_array($permission, $this->getUserPermissions());
	}

}
