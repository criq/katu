<?php

namespace Katu\Models;

use \Katu\Exception;
use \Sexy\CmpNotEq;

class User extends \Katu\Model {

	const TABLE = 'users';

	static function create() {
		return static::insert(array(
			'timeCreated' => (string) (\Katu\Utils\DateTime::get()->getDbDateTimeFormat()),
		));
	}

	static function createWithEmailAddress($emailAddress) {
		if (!$emailAddress || !($emailAddress instanceof \App\Models\EmailAddress)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid e-mail address.", 'emailAddress');
		}

		// Look for another user with this e-mail address.
		if (static::getBy(array(
			'emailAddressId' => $emailAddress->getId(),
		))->getTotal()) {
			throw new \Katu\Exceptions\ArgumentErrorException("E-mail address is already in use.", 'emailAddress');
		}

		$object = static::create();
		$object->setEmailAddress($emailAddress);
		$object->save();

		return $object;
	}

	static function getCurrent() {
		return static::get(\Katu\Session::get('katu.user.id'));
	}

	static function getByAccessToken($token) {
		$accessToken = \App\Models\AccessToken::getOneBy([
			'token' => $token,
			new \Sexy\CmpGreaterThanOrEqual(\App\Models\AccessToken::getColumn('timeExpires'), (new \Katu\Utils\DateTime())->getDbDateTimeFormat()),
		]);

		if ($accessToken) {
			return static::get($accessToken->userId);
		}

		return false;
	}

	public function getValidAccessToken() {
		return \App\Models\AccessToken::makeValidForUser($this);
	}

	public function addUserService($serviceName, $serviceUserId) {
		return \App\Models\UserService::create($this, $serviceName, $serviceUserId);
	}

	public function getDefaultUserServiceByName($serviceName) {
		return \App\Models\UserService::getOneBy(array(
			'userId'      => (int)    ($this->getId()),
			'serviceName' => (string) ($serviceName),
		));
	}

	public function hasPassword() {
		return (bool) $this->password;
	}

	public function hasEmailAddress() {
		return (bool) $this->emailAddressId;
	}

	public function setEmailAddress($emailAddress) {
		if (!$emailAddress || !($emailAddress instanceof \App\Models\EmailAddress)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid e-mail address.", 'emailAddress');
		}

		// Look for another user with this e-mail address.
		if (static::getBy(array(
			'emailAddressId' => $emailAddress->getId(),
			new CmpNotEq(static::getColumn('id'), $this->getId()),
		))->getTotal()) {
			throw new \Katu\Exceptions\ArgumentErrorException("E-mail address is used by another user.", 'emailAddress');
		}

		$this->update('emailAddressId', $emailAddress->getId());

		return true;
	}

	public function setPlainPassword($password, $hash = 'sha512') {
		$this->update('password', \Katu\Utils\Password::encode($hash, $password));

		return true;
	}

	public function setName($name) {
		$this->update('name', trim($name));

		return true;
	}

	public function login() {

		\Katu\Session::set('katu.user.id', (int) $this->getId());

		return true;
	}

	static function logout() {
		\Katu\Utils\Facebook::resetToken();

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

		return true;
	}

	public function hasRole($role) {
		return (bool) \App\Models\UserRole::getOneBy(array(
			'userId' => (int) ($this->getId()),
			'roleId' => (int) ($role->getId()),
		));
	}

	public function deleteAllRoles() {
		foreach (\App\Models\UserRole::getBy(array(
			'userId' => $this->getId(),
		)) as $userRole) {
			$userRole->delete();
		}

		return true;
	}

	public function addUserPermission($permission) {
		return \App\Models\UserPermission::make($this, $permission);
	}

	public function addUserPermissions($permissions) {
		foreach ((array) $permissions as $permission) {
			$this->addUserPermission($permission);
		}

		return true;
	}

	public function deleteAllUserPermissions() {
		foreach (\App\Models\UserPermission::getBy(array(
			'userId' => $this->getId(),
		)) as $userPermission) {
			$userPermission->delete();
		}

		return true;
	}

	static function currentHasPermission($permission) {
		$user = static::getCurrent();
		if (!$user) {
			return false;
		}

		return $user->hasPermission($permission);
	}

	public function getRolePermissions() {
		if (class_exists('\App\Models\RolePermission')) {
			$sql = (new \Sexy\Select(\App\Models\RolePermission::getColumn('permission')))
				->from(\App\Models\RolePermission::getTable())
				->joinColumns(\App\Models\RolePermission::getColumn('roleId'), \App\Models\UserRole::getColumn('roleId'))
				->whereEq(\App\Models\UserRole::getColumn('userId'), $this->getId())
				->groupBy(new \Sexy\GroupBy(\App\Models\RolePermission::getColumn('permission')));

			return static::getPdo()->createQueryFromSql($sql)->getResult()->getColumnValues('permission');
		}

		return false;
	}

	public function getUserPermissions() {
		if (class_exists('\App\Models\UserPermission')) {
			return \App\Models\UserPermission::getBy(array(
				'userId' => (int) ($this->getId()),
			))->getPropertyValues('permission');
		}

		return false;
	}

	public function getAllPermissions() {
		return \Katu\Utils\Cache::getRuntime(['users', $this->getId(), 'allPermissions'], function() {
			return array_filter(array_unique(array_merge((array) $this->getRolePermissions(), (array) $this->getUserPermissions())));
		});
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
