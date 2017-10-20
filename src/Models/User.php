<?php

namespace Katu\Models;

use \Katu\Exception;
use \Sexy\CmpNotEq;

class User extends \Katu\Model {

	const TABLE = 'users';

	static $columnNames = [
		'timeCreated' => 'timeCreated',
		'emailAddressId' => 'emailAddressId',
	];

	static function create() {
		return static::insert([
			static::$columnNames['timeCreated'] => new \Katu\Utils\DateTime,
		]);
	}

	static function createWithEmailAddress($emailAddress) {
		if (!$emailAddress || !($emailAddress instanceof \App\Models\EmailAddress)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid e-mail address."))
				->setAbbr('invalidEmailAddress')
				->addErrorName('emailAddress')
				;
		}

		// Look for another user with this e-mail address.
		if (static::getBy([
			static::$columnNames['emailAddressId'] => $emailAddress->getId(),
		])->getTotal()) {
			throw (new \Katu\Exceptions\InputErrorException("E-mail address is already in use."))
				->setAbbr('emailAddressInUse')
				->addErrorName('emailAddress')
				;
		}

		return static::insert([
			static::$columnNames['timeCreated'] => (string) (\Katu\Utils\DateTime::get()->getDbDateTimeFormat()),
			static::$columnNames['emailAddressId'] => (int) ($emailAddress->getId()),
		]);
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
		return \App\Models\UserService::getOneBy([
			'userId'      => (int)    ($this->getId()),
			'serviceName' => (string) ($serviceName),
		]);
	}

	public function hasPassword() {
		return (bool) $this->password;
	}

	public function getEmailAddress() {
		return \App\Models\EmailAddress::get($this->{static::$columnNames['emailAddressId']});
	}

	public function hasEmailAddress() {
		return (bool) $this->{static::$columnNames['emailAddressId']};
	}

	public function setEmailAddress($emailAddress) {
		if (!$emailAddress || !($emailAddress instanceof \App\Models\EmailAddress)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid e-mail address."))
				->setAbbr('invalidEmailAddress')
				->addErrorName('emailAddress')
				;
		}

		// Look for another user with this e-mail address.
		if (static::getBy([
			static::$columnNames['emailAddressId'] => $emailAddress->getId(),
			new CmpNotEq(static::getIdColumn(), $this->getId()),
		])->getTotal()) {
			throw (new \Katu\Exceptions\InputErrorException("E-mail address is used by another user."))
				->setAbbr('emailAddressInUse')
				->addErrorName('emailAddress')
				;
		}

		$this->update(static::$columnNames['emailAddressId'], $emailAddress->getId());

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
		\Katu\Session::reset('katu.user.id');
		\Katu\Session::reset(\Katu\Utils\Facebook::ACCESS_TOKEN_SESSION_KEY);
		\Katu\Cookie::remove('accessToken');

		return true;
	}

	public function addRole($role) {
		return \App\Models\UserRole::make($this, $role);
	}

	public function addRolesByIds($roleIds) {
		$roles = [];

		foreach ((array) $roleIds as $roleId) {
			$role = \App\Models\Role::get($roleId);
			if (!$role) {
				throw (new \Katu\Exceptions\InputErrorException("Invalid role ID."))
					->setAbbr('invalidRoleId')
					;
			}

			$roles[] = $role;
		}

		foreach ($roles as $role) {
			$this->addRole($role);
		}

		return true;
	}

	public function hasRole($role) {
		return (bool) \App\Models\UserRole::getOneBy([
			'userId' => (int) ($this->getId()),
			'roleId' => (int) ($role->getId()),
		]);
	}

	public function deleteAllRoles() {
		foreach (\App\Models\UserRole::getBy([
			'userId' => $this->getId(),
		]) as $userRole) {
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
		foreach (\App\Models\UserPermission::getBy([
			'userId' => $this->getId(),
		]) as $userPermission) {
			$userPermission->delete();
		}

		return true;
	}

	static function currentHasPermission() {
		$user = static::getCurrent();
		if (!$user) {
			return false;
		}

		return call_user_func_array([$user, 'hasPermission'], func_get_args());
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
			return \App\Models\UserPermission::getBy([
				'userId' => (int) ($this->getId()),
			])->getPropertyValues('permission');
		}

		return false;
	}

	public function getAllPermissions() {
		return \Katu\Utils\Cache::getRuntime(['users', $this->getId(), 'allPermissions'], function() {
			return array_filter(array_unique(array_merge((array) $this->getRolePermissions(), (array) $this->getUserPermissions())));
		});
	}

	public function hasPermission() {
		$args = func_get_args();
		$permissions = is_string($args[0]) ? [$args[0]] : $args[0];
		$any = isset($args[1]) ? $args[1] : false;

		$status = [];
		foreach ($permissions as $permission) {
			$status[$permission] = in_array($permission, $this->getAllPermissions());
		}

		if ($any) {
			return in_array(true, $status);
		}

		return !in_array(false, $status);
	}

	public function hasRolePermission($permission) {
		return in_array($permission, $this->getRolePermissions());
	}

	public function hasUserPermission($permission) {
		return in_array($permission, $this->getUserPermissions());
	}

	public function setUserSetting($name, $value) {
		$userSetting = \App\Models\UserSetting::make($this, $name);
		$userSetting->setValue($value);
		$userSetting->save();

		return true;
	}

	public function getUserSetting($name) {
		return \App\Models\UserSetting::getOneBy([
			'userId' => $this->getId(),
			'name' => $name,
		]);
	}

	public function getUserSettingValue($name) {
		$userSetting = $this->getUserSetting($name);

		return $userSetting ? $userSetting->value : null;
	}

}
