<?php

namespace Katu\Models\Presets;

use Katu\Tools\DateTime\Timeout;
use Katu\Types\TClass;
use Katu\Types\TIdentifier;
use Sexy\Sexy as SX;

class User extends \Katu\Models\Model
{
	const TABLE = 'users';

	public static $columnNames = [
		'emailAddressId' => 'emailAddressId',
		'timeCreated' => 'timeCreated',
	];

	/****************************************************************************
	 * Classes.
	 */
	public static function getAccessTokenClass() : TClass
	{
		return new TClass("App\Models\AccessToken");
	}

	public static function getEmailAddressClass() : TClass
	{
		return new TClass("App\Models\EmailAddress");
	}

	public static function getRoleClass() : TClass
	{
		return new TClass("App\Models\Role");
	}

	public static function getRolePermissionClass() : TClass
	{
		return new TClass("App\Models\RolePermission");
	}

	public static function getUserRoleClass() : TClass
	{
		return new TClass("App\Models\UserRole");
	}

	public static function getUserPermissionClass() : TClass
	{
		return new TClass("App\Models\UserPermission");
	}

	public static function getUserServiceClass() : TClass
	{
		return new TClass("App\Models\UserService");
	}

	public static function getUserSettingClass() : TClass
	{
		return new TClass("App\Models\UserSetting");
	}

	/****************************************************************************
	 * Create & Delete.
	 */
	public static function create() : User
	{
		return static::insert([
			static::$columnNames['timeCreated'] => new \Katu\Tools\DateTime\DateTime,
		]);
	}

	public static function createWithEmailAddress(\Katu\Models\Presets\EmailAddress $emailAddress) : User
	{
		if (static::getBy([
			static::$columnNames['emailAddressId'] => $emailAddress->getId(),
		])->getTotal()) {
			throw (new \Katu\Exceptions\InputErrorException("E-mail address is already in use."))
				->setAbbr('emailAddressInUse')
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

	public static function getByAccessToken(?string $token) : ?User
	{
		$accessTokenClass = static::getAccessTokenClass()->getName();

		$accessToken = $accessTokenClass::getOneBy([
			'token' => preg_replace('/^(Bearer)\s+/', '', $token),
			SX::cmpGreaterThanOrEqual($accessTokenClass::getColumn('timeExpires'), new \Katu\Tools\DateTime\DateTime),
		]);

		if ($accessToken) {
			return static::get($accessToken->userId);
		}

		return null;
	}

	/****************************************************************************
	 * Getters & Setters.
	 */
	public function setName(?string $name) : User
	{
		$this->update('name', trim($name) ?: null);

		return $this;
	}

	public function getName() : ?string
	{
		return $this->name;
	}

	public function setEmailAddress(?\Katu\Models\Presets\EmailAddress $emailAddress)
	{
		$emailAddressClass = static::getEmailAddressClass()->getName();
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

	public function getEmailAddress()
	{
		return static::getEmailAddressClass()->getName()::get($this->{static::$columnNames['emailAddressId']});
	}

	public function setPlainPassword(string $password)
	{
		$this->update('password', (new \Katu\Tools\Security\Password($password))->getEncoded());

		return $this;
	}

	public function getPassword()
	{
		try {
			return \Katu\Tools\Security\Password::createFromEncoded($this->password);
		} catch (\Throwable $e) {
			return false;
		}
	}

	public function getValidAccessToken()
	{
		return static::getAccessTokenClass()->getName()::makeValidForUser($this);
	}

	public function addUserService($serviceName, $serviceUserId)
	{
		return static::getUserServiceClass()->getName()::create($this, $serviceName, $serviceUserId);
	}

	public function makeUserService($serviceName, $serviceUserId)
	{
		return static::getUserServiceClass()->getName()::upsert([
			'userId' => $this->getId(),
			'serviceName' => (string)$serviceName,
			'serviceUserId' => (string)$serviceUserId,
		], [
			'timeCreated' => new \Katu\Tools\DateTime\DateTime,
		]);
	}

	public function getDefaultUserServiceByName($serviceName)
	{
		return static::getUserServiceClass()->getName()::getOneBy([
			'userId' => $this->getId(),
			'serviceName' => (string)$serviceName,
		]);
	}

	public function hasPassword()
	{
		return (bool) $this->password;
	}

	public function hasEmailAddress()
	{
		return (bool) $this->{static::$columnNames['emailAddressId']};
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
		return static::getUserRoleClass()->getName()::make($this, $role);
	}

	public function addRolesByIds($roleIds)
	{
		$roles = [];
		foreach ((array) $roleIds as $roleId) {
			$role = static::getRoleClass()->getName()::get($roleId);
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
		return (bool)static::getUserRoleClass()->getName()::getOneBy([
			'userId' => $this->getId(),
			'roleId' => $role->getId(),
		]);
	}

	public function getUserRoles()
	{
		return static::getUserRoleClass()->getName()::getBy([
			'userId' => $this->getId(),
		]);
	}

	public function deleteAllRoles()
	{
		foreach (static::getUserRoleClass()->getName()::getBy([
			'userId' => $this->getId(),
		]) as $userRole) {
			$userRole->delete();
		}

		return true;
	}

	public function addUserPermission($permission)
	{
		return static::getUserPermissionClass()->getName()::make($this, $permission);
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
		foreach (static::getUserPermissionClass()->getName()::getBy([
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
		$userRoleClass = static::getUserRoleClass()->getName();
		$rolePermissionClass = static::getRolePermissionClass()->getName();

		$sql = (SX::select($rolePermissionClass::getColumn('permission')))
			->from($rolePermissionClass::getTable())
			->joinColumns($rolePermissionClass::getColumn('roleId'), $userRoleClass::getColumn('roleId'))
			->where(SX::eq($userRoleClass::getColumn('userId'), $this->getId()))
			->groupBy(SX::groupBy($rolePermissionClass::getColumn('permission')))
			;

		return static::getConnection()->select($sql)->getResult()->getColumnValues('permission');
	}

	public function getUserPermissions()
	{
		return static::getUserPermissionClass()->getName()::getBy([
			'userId' => $this->getId(),
		])->getColumnValues('permission');
	}

	public function getAllPermissions(): array
	{
		return \Katu\Cache\General::get(new TIdentifier('users', $this->getId(), 'allPermissions'), new Timeout('1 second'), function () {
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
		return static::getUserSettingClass()->getName()::getOrCreate($this, $name, $value);
	}

	public function getUserSetting($name)
	{
		return static::getUserSettingClass()->getName()::getOneBy([
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
