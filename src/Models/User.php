<?php

namespace Katu\Models;

class User extends \Katu\Model
{
	const TABLE = 'users';

	public static $columnNames = [
		'timeCreated' => 'timeCreated',
		'emailAddressId' => 'emailAddressId',
	];

	public static function getAccessTokenClass()
	{
		return '\\App\\Models\\AccessToken';
	}

	public static function getEmailAddressClass()
	{
		return '\\App\\Models\\EmailAddress';
	}

	public static function getRoleClass()
	{
		return '\\App\\Models\\Role';
	}

	public static function getRolePermissionClass()
	{
		return '\\App\\Models\\RolePermission';
	}

	public static function getUserRoleClass()
	{
		return '\\App\\Models\\UserRole';
	}

	public static function getUserPermissionClass()
	{
		return '\\App\\Models\\UserPermission';
	}

	public static function getUserServiceClass()
	{
		return '\\App\\Models\\UserService';
	}

	public static function getUserSettingClass()
	{
		return '\\App\\Models\\UserSetting';
	}

	public static function create()
	{
		return static::insert([
			static::$columnNames['timeCreated'] => new \Katu\Utils\DateTime,
		]);
	}

	public static function createWithEmailAddress($emailAddress)
	{
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

	public static function getCurrent()
	{
		return static::get(\Katu\Session::get('katu.user.id'));
	}

	public static function getByAccessToken($token)
	{
		$token = preg_replace('/^Bearer\s+/', null, $token);

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

	public function getValidAccessToken()
	{
		$accessTokenClass = static::getAccessTokenClass();

		return $accessTokenClass::makeValidForUser($this);
	}

	public function addUserService($serviceName, $serviceUserId)
	{
		$userServiceClass = static::getUserServiceClass();

		return $userServiceClass::create($this, $serviceName, $serviceUserId);
	}

	public function makeUserService($serviceName, $serviceUserId)
	{
		$userServiceClass = static::getUserServiceClass();

		return $userServiceClass::upsert([
			'userId'        => (int)$this->getId(),
			'serviceName'   => (string)$serviceName,
			'serviceUserId' => (string)$serviceUserId,
		], [
			'timeCreated'   => (string)(new \Katu\Utils\DateTime),
		]);
	}

	public function getDefaultUserServiceByName($serviceName)
	{
		$userServiceClass = static::getUserServiceClass();

		return $userServiceClass::getOneBy([
			'userId'      => (int)    ($this->getId()),
			'serviceName' => (string) ($serviceName),
		]);
	}

	public function hasPassword()
	{
		return (bool) $this->password;
	}

	public function getEmailAddress()
	{
		$emailAddressClass = static::getEmailAddressClass();

		return $emailAddressClass::get($this->{static::$columnNames['emailAddressId']});
	}

	public function hasEmailAddress()
	{
		return (bool) $this->{static::$columnNames['emailAddressId']};
	}

	public function setEmailAddress($emailAddress)
	{
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
			new \Sexy\CmpNotEq(static::getIdColumn(), $this->getId()),
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
		$this->update('password', \Katu\Utils\Password::encode($hash, $password));

		return true;
	}

	public function setName($name)
	{
		$this->update('name', trim($name));

		return true;
	}

	public function getName()
	{
		return $this->name;
	}

	public function login()
	{
		\Katu\Session::set('katu.user.id', (int)$this->getId());

		return true;
	}

	public static function logout()
	{
		\Katu\Session::reset('katu.user.id');
		\Katu\Cookie::remove('accessToken');

		return true;
	}

	public function addRole($role)
	{
		$userRoleClass = static::getUserRoleClass();

		return $userRoleClass::make($this, $role);
	}

	public function addRolesByIds($roleIds)
	{
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

	public function hasRole($role)
	{
		$userRoleClass = static::getUserRoleClass();

		return (bool)$userRoleClass::getOneBy([
			'userId' => (int) ($this->getId()),
			'roleId' => (int) ($role->getId()),
		]);
	}

	public function getUserRoles()
	{
		$userRoleClass = static::getUserRoleClass();

		return $userRoleClass::getBy([
			'userId' => $this->getId(),
		]);
	}

	public function deleteAllRoles()
	{
		$userRoleClass = static::getUserRoleClass();

		foreach ($userRoleClass::getBy([
			'userId' => $this->getId(),
		]) as $userRole) {
			$userRole->delete();
		}

		return true;
	}

	public function addUserPermission($permission)
	{
		$userPermissionClass = static::getUserPermissionClass();

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
		$userPermissionClass = static::getUserPermissionClass();

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

	public function getUserPermissions()
	{
		$userPermissionClass = static::getUserPermissionClass();

		if (class_exists($userPermissionClass)) {
			return $userPermissionClass::getBy([
				'userId' => (int) ($this->getId()),
			])->getPropertyValues('permission');
		}

		return false;
	}

	public function getAllPermissions()
	{
		return \Katu\Utils\Cache::getRuntime(['users', $this->getId(), 'allPermissions'], function () {
			return array_filter(array_unique(array_merge((array)$this->getRolePermissions(), (array)$this->getUserPermissions())));
		});
	}

	public function hasPermission($permission)
	{
		return in_array($permission, $this->getAllPermissions());
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
		$userSettingClass = static::getUserSettingClass();

		$userSetting = $userSettingClass::make($this, $name);
		$userSetting->setValue($value);
		$userSetting->save();

		return true;
	}

	public function getUserSetting($name)
	{
		$userSettingClass = static::getUserSettingClass();

		return $userSettingClass::getOneBy([
			'userId' => $this->getId(),
			'name' => $name,
		]);
	}

	public function getUserSettingValue($name)
	{
		$userSetting = $this->getUserSetting($name);

		return $userSetting ? $userSetting->value : null;
	}
}
