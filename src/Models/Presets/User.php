<?php

namespace Katu\Models\Presets;

use \Sexy\Sexy as SX;

class User extends \Katu\Models\Model
{
	const TABLE = 'users';

	public static $columnNames = [
		'emailAddressId' => 'emailAddressId',
		'timeCreated' => 'timeCreated',
	];

	public static function getAccessTokenClassName() : \Katu\Tools\Classes\ClassName
	{
		return new \Katu\Tools\Classes\ClassName('App', 'Models', 'AccessToken');
	}

	public static function getEmailAddressClassName() : \Katu\Tools\Classes\ClassName
	{
		return new \Katu\Tools\Classes\ClassName('App', 'Models', 'EmailAddress');
	}

	public static function getRoleClassName() : \Katu\Tools\Classes\ClassName
	{
		return new \Katu\Tools\Classes\ClassName('App', 'Models', 'Role');
	}

	public static function getRolePermissionClassName() : \Katu\Tools\Classes\ClassName
	{
		return new \Katu\Tools\Classes\ClassName('App', 'Models', 'RolePermission');
	}

	public static function getUserRoleClassName() : \Katu\Tools\Classes\ClassName
	{
		return new \Katu\Tools\Classes\ClassName('App', 'Models', 'UserRole');
	}

	public static function getUserPermissionClassName() : \Katu\Tools\Classes\ClassName
	{
		return new \Katu\Tools\Classes\ClassName('App', 'Models', 'UserPermission');
	}

	public static function getUserServiceClassName() : \Katu\Tools\Classes\ClassName
	{
		return new \Katu\Tools\Classes\ClassName('App', 'Models', 'UserService');
	}

	public static function getUserSettingClassName() : \Katu\Tools\Classes\ClassName
	{
		return new \Katu\Tools\Classes\ClassName('App', 'Models', 'UserSetting');
	}

	public static function create()
	{
		return static::insert([
			static::$columnNames['timeCreated'] => new \Katu\Tools\DateTime\DateTime,
		]);
	}

	public static function createWithEmailAddress($emailAddress)
	{
		$emailAddressClass = (string)static::getEmailAddressClassName();

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
			static::$columnNames['timeCreated'] => (string)\Katu\Tools\DateTime\DateTime::get()->getDbDateTimeFormat(),
			static::$columnNames['emailAddressId'] => (int)$emailAddress->getId(),
		]);
	}

	public static function getCurrent()
	{
		return static::get(\Katu\Tools\Session\Session::get('katu.user.id'));
	}

	public static function getByAccessToken(string $token = null)
	{
		$accessTokenClass = (string)static::getAccessTokenClassName();

		$accessToken = $accessTokenClass::getOneBy([
			'token' => preg_replace('/^(Bearer)\s+/', null, $token),
			new \Sexy\CmpGreaterThanOrEqual($accessTokenClass::getColumn('timeExpires'), new \Katu\Tools\DateTime\DateTime),
		]);

		if ($accessToken) {
			return static::get($accessToken->userId);
		}

		return false;
	}

	public function getValidAccessToken()
	{
		$accessTokenClass = (string)static::getAccessTokenClassName();

		return $accessTokenClass::makeValidForUser($this);
	}

	public function addUserService($serviceName, $serviceUserId)
	{
		$userServiceClass = (string)static::getUserServiceClassName();

		return $userServiceClass::create($this, $serviceName, $serviceUserId);
	}

	public function makeUserService($serviceName, $serviceUserId)
	{
		$userServiceClass = (string)static::getUserServiceClassName();

		return $userServiceClass::upsert([
			'userId' => $this->getId(),
			'serviceName' => (string)$serviceName,
			'serviceUserId' => (string)$serviceUserId,
		], [
			'timeCreated' => new \Katu\Tools\DateTime\DateTime,
		]);
	}

	public function getDefaultUserServiceByName($serviceName)
	{
		$userServiceClass = (string)static::getUserServiceClassName();

		return $userServiceClass::getOneBy([
			'userId' => $this->getId(),
			'serviceName' => (string)$serviceName,
		]);
	}

	public function hasPassword()
	{
		return (bool) $this->password;
	}

	public function getEmailAddress()
	{
		$emailAddressClass = (string)static::getEmailAddressClassName();

		return $emailAddressClass::get($this->{static::$columnNames['emailAddressId']});
	}

	public function hasEmailAddress()
	{
		return (bool) $this->{static::$columnNames['emailAddressId']};
	}

	public function setEmailAddress($emailAddress)
	{
		$emailAddressClass = (string)static::getEmailAddressClassName();

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

	public function setPlainPassword($password, $hash = 'sha512')
	{
		$this->update('password', (new \Katu\Tools\Security\Password($password))->getEncoded());

		return true;
	}

	public function getPassword()
	{
		try {
			return \Katu\Tools\Security\Password::createFromEncoded($this->password);
		} catch (\Throwable $e) {
			return false;
		}
	}

	public function setName($name)
	{
		$this->update('name', trim($name));

		return true;
	}

	public function login()
	{
		\Katu\Tools\Session\Session::set('katu.user.id', (int)$this->getId());

		return true;
	}

	public static function logout()
	{
		\Katu\Tools\Session\Session::reset('katu.user.id');
		\Katu\Tools\Cookies\Cookie::remove('accessToken');

		return true;
	}

	public function addRole($role)
	{
		$userRoleClass = (string)static::getUserRoleClassName();

		return $userRoleClass::make($this, $role);
	}

	public function addRolesByIds($roleIds)
	{
		$roleClass = (string)static::getRoleClassName();

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

	public function hasRole($role)
	{
		$userRoleClass = (string)static::getUserRoleClassName();

		return (bool)$userRoleClass::getOneBy([
			'userId' => $this->getId(),
			'roleId' => $role->getId(),
		]);
	}

	public function getUserRoles()
	{
		$userRoleClass = (string)static::getUserRoleClassName();

		return $userRoleClass::getBy([
			'userId' => $this->getId(),
		]);
	}

	public function deleteAllRoles()
	{
		$userRoleClass = (string)static::getUserRoleClassName();

		foreach ($userRoleClass::getBy([
			'userId' => $this->getId(),
		]) as $userRole) {
			$userRole->delete();
		}

		return true;
	}

	public function addUserPermission($permission)
	{
		$userPermissionClass = (string)static::getUserPermissionClassName();

		return $userPermissionClass::make($this, $permission);
	}

	public function addUserPermissions($permissions)
	{
		foreach ((array) $permissions as $permission) {
			$this->addUserPermission($permission);
		}

		return true;
	}

	public function deleteAllUserPermissions()
	{
		$userPermissionClass = (string)static::getUserPermissionClassName();

		foreach ($userPermissionClass::getBy([
			'userId' => $this->getId(),
		]) as $userPermission) {
			$userPermission->delete();
		}

		return true;
	}

	public static function currentHasPermission()
	{
		$user = static::getCurrent();
		if (!$user) {
			return false;
		}

		return call_user_func_array([$user, 'hasPermission'], func_get_args());
	}

	public function getRolePermissions()
	{
		$userRoleClass = (string)static::getUserRoleClassName();
		$rolePermissionClass = (string)static::getRolePermissionClassName();

		if (class_exists($rolePermissionClass)) {
			$sql = (new \Sexy\Select($rolePermissionClass::getColumn('permission')))
				->from($rolePermissionClass::getTable())
				->joinColumns($rolePermissionClass::getColumn('roleId'), $userRoleClass::getColumn('roleId'))
				->whereEq($userRoleClass::getColumn('userId'), $this->getId())
				->groupBy(new \Sexy\GroupBy($rolePermissionClass::getColumn('permission')))
				;

			return static::getConnection()->select($sql)->getResult()->getColumnValues('permission');
		}

		return false;
	}

	public function getUserPermissions()
	{
		$userPermissionClass = (string)static::getUserPermissionClassName();

		if (class_exists($userPermissionClass)) {
			return $userPermissionClass::getBy([
				'userId' => $this->getId(),
			])->getPropertyValues('permission');
		}

		return false;
	}

	public function getAllPermissions()
	{
		return \Katu\Cache\Runtime::get(['users', $this->getId(), 'allPermissions'], function () {
			return array_filter(array_unique(array_merge((array)$this->getRolePermissions(), (array)$this->getUserPermissions())));
		});
	}

	public function hasPermission()
	{
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

	public function hasRolePermission($permission)
	{
		return in_array($permission, $this->getRolePermissions());
	}

	public function hasUserPermission($permission)
	{
		return in_array($permission, $this->getUserPermissions());
	}

	public function setUserSetting($name, $value)
	{
		$userSettingClass = (string)static::getUserSettingClassName();

		return $userSettingClass::getOrCreate($this, $name, $value);
	}

	public function getUserSetting($name)
	{
		$userSettingClass = (string)static::getUserSettingClassName();

		return $userSettingClass::getOneBy([
			'userId' => $this->getId(),
			'name' => $name,
		]);
	}

	public function getUserSettingValue($name)
	{
		$userSetting = $this->getUserSetting($name);

		return $userSetting ? $userSetting->getValue() : null;
	}
}
