<?php

namespace Katu\Models\Models;

use \Sexy\Sexy as SX;

class User extends \Katu\Models\Model {

	const TABLE = 'users';

	static $columnNames = [
		'timeCreated' => 'timeCreated',
		'emailAddressId' => 'emailAddressId',
	];

	static function getAccessTokenClass() {
		return '\\App\\Models\\AccessToken';
	}

	static function getEmailAddressClass() {
		return '\\App\\Models\\EmailAddress';
	}

	static function getRoleClass() {
		return '\\App\\Models\\Role';
	}

	static function getRolePermissionClass() {
		return '\\App\\Models\\RolePermission';
	}

	static function getUserRoleClass() {
		return '\\App\\Models\\UserRole';
	}

	static function getUserPermissionClass() {
		return '\\App\\Models\\UserPermission';
	}

	static function getUserServiceClass() {
		return '\\App\\Models\\UserService';
	}

	static function getUserSettingClass() {
		return '\\App\\Models\\UserSetting';
	}

	static function create() {
		return static::insert([
			static::$columnNames['timeCreated'] => new \Katu\Utils\DateTime,
		]);
	}

	static function createWithEmailAddress($emailAddress) {
		$emailAddressClass = static::getEmailAddressClass();

		if (!$emailAddress || !($emailAddress instanceof $emailAddressClass)) {
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
			static::$columnNames['timeCreated'] => (string)\Katu\Utils\DateTime::get()->getDbDateTimeFormat(),
			static::$columnNames['emailAddressId'] => (int)$emailAddress->getId(),
		]);
	}

	static function getCurrent() {
		return static::get(\Katu\Session::get('katu.user.id'));
	}

	static function getByAccessToken($token) {
		$accessTokenClass = static::getAccessTokenClass();

		$accessToken = $accessTokenClass::getOneBy([
			'token' => $token,
			new \Sexy\CmpGreaterThanOrEqual($accessTokenClass::getColumn('timeExpires'), new \Katu\Utils\DateTime),
		]);

		if ($accessToken) {
			return static::get($accessToken->userId);
		}

		return false;
	}

	public function getValidAccessToken() {
		$accessTokenClass = static::getAccessTokenClass();

		return $accessTokenClass::makeValidForUser($this);
	}

	public function addUserService($serviceName, $serviceUserId) {
		$userServiceClass = static::getUserServiceClass();

		return $userServiceClass::create($this, $serviceName, $serviceUserId);
	}

	public function makeUserService($serviceName, $serviceUserId) {
		$userServiceClass = static::getUserServiceClass();

		return $userServiceClass::upsert([
			'userId'        => (int)$this->getId(),
			'serviceName'   => (string)$serviceName,
			'serviceUserId' => (string)$serviceUserId,
		], [
			'timeCreated'   => (string)(new \Katu\Utils\DateTime),
		]);
	}

	public function getDefaultUserServiceByName($serviceName) {
		return $userServiceClass::getOneBy([
			'userId'      => (int)    ($this->getId()),
			'serviceName' => (string) ($serviceName),
		]);
	}

	public function hasPassword() {
		return (bool) $this->password;
	}

	public function getEmailAddress() {
		$emailAddressClass = static::getEmailAddressClass();

		return $emailAddressClass::get($this->{static::$columnNames['emailAddressId']});
	}

	public function hasEmailAddress() {
		return (bool) $this->{static::$columnNames['emailAddressId']};
	}

	public function setEmailAddress($emailAddress) {
		$emailAddressClass = static::getEmailAddressClass();

		if (!$emailAddress || !($emailAddress instanceof $emailAddressClass)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid e-mail address."))
				->setAbbr('invalidEmailAddress')
				->addErrorName('emailAddress')
				;
		}

		// Look for another user with this e-mail address.
		if (static::getBy([
			static::$columnNames['emailAddressId'] => $emailAddress->getId(),
			SX::cmpNotEq(static::getIdColumn(), $this->getId()),
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
		\Katu\Session::set('katu.user.id', (int)$this->getId());

		return true;
	}

	static function logout() {
		\Katu\Session::reset('katu.user.id');
		\Katu\Cookie::remove('accessToken');

		return true;
	}

	public function addRole($role) {
		$userRoleClass = static::getUserRoleClass();

		return $userRoleClass::make($this, $role);
	}

	public function addRolesByIds($roleIds) {
		$roleClass = static::getRoleClass();

		$roles = [];

		foreach ((array) $roleIds as $roleId) {
			$role = $roleClass::get($roleId);
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
		$userRoleClass = static::getUserRoleClass();

		return (bool)$userRoleClass::getOneBy([
			'userId' => (int) ($this->getId()),
			'roleId' => (int) ($role->getId()),
		]);
	}

	public function getUserRoles() {
		$userRoleClass = static::getUserRoleClass();

		return $userRoleClass::getBy([
			'userId' => $this->getId(),
		]);
	}

	public function deleteAllRoles() {
		$userRoleClass = static::getUserRoleClass();

		foreach ($userRoleClass::getBy([
			'userId' => $this->getId(),
		]) as $userRole) {
			$userRole->delete();
		}

		return true;
	}

	public function addUserPermission($permission) {
		$userPermissionClass = static::getUserPermissionClass();

		return $userPermissionClass::make($this, $permission);
	}

	public function addUserPermissions($permissions) {
		foreach ((array) $permissions as $permission) {
			$this->addUserPermission($permission);
		}

		return true;
	}

	public function deleteAllUserPermissions() {
		$userPermissionClass = static::getUserPermissionClass();

		foreach ($userPermissionClass::getBy([
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
		$userRoleClass = static::getUserRoleClass();
		$rolePermissionClass = static::getRolePermissionClass();

		if (class_exists($rolePermissionClass)) {
			$sql = (new \Sexy\Select($rolePermissionClass::getColumn('permission')))
				->from($rolePermissionClass::getTable())
				->joinColumns($rolePermissionClass::getColumn('roleId'), $userRoleClass::getColumn('roleId'))
				->whereEq($userRoleClass::getColumn('userId'), $this->getId())
				->groupBy(new \Sexy\GroupBy($rolePermissionClass::getColumn('permission')));

			return static::getPdo()->createQueryFromSql($sql)->getResult()->getColumnValues('permission');
		}

		return false;
	}

	public function getUserPermissions() {
		$userPermissionClass = static::getUserPermissionClass();

		if (class_exists($userPermissionClass)) {
			return $userPermissionClass::getBy([
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
		foreach ((array)$permissions as $permission) {
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
		$userSettingClass = static::getUserSettingClass();

		$userSetting = $userSettingClass::make($this, $name);
		$userSetting->setValue($value);
		$userSetting->save();

		return true;
	}

	public function getUserSetting($name) {
		$userSettingClass = static::getUserSettingClass();

		return $userSettingClass::getOneBy([
			'userId' => $this->getId(),
			'name' => $name,
		]);
	}

	public function getUserSettingValue($name) {
		$userSetting = $this->getUserSetting($name);

		return $userSetting ? $userSetting->value : null;
	}

}
