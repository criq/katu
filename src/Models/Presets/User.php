<?php

namespace Katu\Models\Presets;

use Katu\Tools\Calendar\Time;
use Katu\Types\TIdentifier;
use Psr\Http\Message\ServerRequestInterface;
use Sexy\Sexy as SX;

abstract class User extends \Katu\Models\Model
{
	const TABLE = "users";

	public static $columnNames = [
		"emailAddressId" => "emailAddressId",
		"timeCreated" => "timeCreated",
	];

	/****************************************************************************
	 * Create & Delete.
	 */
	public static function getOrCreateWithEmailAddress(\Katu\Models\Presets\EmailAddress $emailAddress): User
	{
		$user = static::getOneBy([
			static::$columnNames["emailAddressId"] => $emailAddress->getId(),
		]);
		if (!$user) {
			static::createWithEmailAddress($emailAddress);

			$user = static::getOneBy([
				static::$columnNames["emailAddressId"] => $emailAddress->getId(),
			]);
		}

		return $user;
	}

	public static function createWithEmailAddress(\Katu\Models\Presets\EmailAddress $emailAddress): User
	{
		if (static::getBy([
			static::$columnNames["emailAddressId"] => $emailAddress->getId(),
		])->getTotal()) {
			throw (new \Katu\Exceptions\InputErrorException("E-mail address is already in use."))
				->setAbbr("emailAddressInUse")
				;
		}

		return static::insert([
			static::$columnNames["timeCreated"] => new Time,
			static::$columnNames["emailAddressId"] => $emailAddress->getId(),
		]);
	}

	public static function getFromRequest(?ServerRequestInterface $request): ?User
	{
		if ($request) {
			// Cookie.
			$user = static::getByAccessToken($request->getCookieParams()["accessToken"] ?? null);
			if ($user) {
				return $user;
			}

			// Access token.
			$header = $request->getHeaderLine("Authorization") ?: $request->getHeaderLine("X-Auth");
			$user = static::getByAccessToken($header);
			if ($user) {
				return $user;
			}
		}

		return null;
	}

	public static function getByAccessToken(?string $token): ?User
	{
		$accessTokenClass = \App\App::getAccessTokenModelClass()->getName();
		$accessToken = $accessTokenClass::getOneBy([
			"token" => preg_replace("/^(Bearer)\s+/", "", $token),
		]);
		if ($accessToken && $accessToken->getIsValid()) {
			return $accessToken->getUser();
		}

		return null;
	}

	/****************************************************************************
	 * Getters & Setters.
	 */
	public function setName($name)
	{
		$this->name = trim($name) ?: null;

		return $this;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function setEmailAddress($emailAddress)
	{
		$emailAddressClassName = \App\App::getEmailAddressModelClass()->getName();
		if (!$emailAddress || !($emailAddress instanceof $emailAddressClassName)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid e-mail address."))
				->setAbbr("invalidEmailAddress")
				->addErrorName("emailAddress")
				;
		}

		// Look for another user with this e-mail address.
		if (static::getBy([
			static::$columnNames["emailAddressId"] => $emailAddress->getId(),
			SX::cmpNotEq(static::getIdColumn(), $this->getId()),
		])->getTotal()) {
			throw (new \Katu\Exceptions\InputErrorException("E-mail address is used by another user."))
				->setAbbr("emailAddressInUse")
				->addErrorName("emailAddress")
				;
		}

		$this->static::$columnNames["emailAddressId"] = $emailAddress->getId();

		return true;
	}

	public function getEmailAddress()
	{
		return \App\App::getEmailAddressModelClass()->getName()::get($this->{static::$columnNames["emailAddressId"]});
	}

	public function setPlainPassword(string $password)
	{
		$this->password = (new \Katu\Tools\Security\PasswordEncoder($password))->getEncoded();

		return $this;
	}

	public function getEncodedPassword(): ?string
	{
		return $this->password;
	}

	public function hasEncodedPassword()
	{
		return (bool)$this->password;
	}

	public function getPasswordEncoder(): \Katu\Tools\Security\PasswordEncoder
	{
		return \Katu\Tools\Security\PasswordEncoder::createFromEncoded($this->getEncodedPassword());
	}

	public function createAccessToken(): AccessToken
	{
		return \App\App::getAccessTokenModelClass()->getName()::create($this);
	}

	public function getSafeAccessToken(): AccessToken
	{
		return \App\App::getAccessTokenModelClass()->getName()::getOrCreateSafe($this);
	}

	public function addUserService($serviceName, $serviceUserId)
	{
		return \App\App::getUserServiceModelClass()->getName()::create($this, $serviceName, $serviceUserId);
	}

	public function makeUserService($serviceName, $serviceUserId)
	{
		return \App\App::getUserServiceModelClass()->getName()::upsert([
			"userId" => $this->getId(),
			"serviceName" => (string)$serviceName,
			"serviceUserId" => (string)$serviceUserId,
		], [
			"timeCreated" => new Time,
		]);
	}

	public function getDefaultUserServiceByName($serviceName)
	{
		return \App\App::getUserServiceModelClass()->getName()::getOneBy([
			"userId" => $this->getId(),
			"serviceName" => (string)$serviceName,
		]);
	}

	public function hasEmailAddress()
	{
		return (bool) $this->{static::$columnNames["emailAddressId"]};
	}

	public function login(): bool
	{
		$this->createAccessToken()->setCookie();

		return true;
	}

	public static function logout(): bool
	{
		\Katu\Tools\Cookies\Cookie::remove("accessToken");

		return true;
	}

	public function addRole($role)
	{
		return \App\App::getUserRoleModelClass()->getName()::make($this, $role);
	}

	public function addRolesByIds($roleIds)
	{
		$roles = [];
		foreach ((array) $roleIds as $roleId) {
			$role = \App\App::getRoleModelClass()->getName()::get($roleId);
			if (!$role) {
				throw (new \Katu\Exceptions\InputErrorException("Invalid role ID."))
					->setAbbr("invalidRoleId")
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
		return (bool)\App\App::getUserRoleModelClass()->getName()::getOneBy([
			"userId" => $this->getId(),
			"roleId" => $role->getId(),
		]);
	}

	public function getUserRoles()
	{
		return \App\App::getUserRoleModelClass()->getName()::getBy([
			"userId" => $this->getId(),
		]);
	}

	public function deleteAllRoles()
	{
		foreach (\App\App::getUserRoleModelClass()->getName()::getBy([
			"userId" => $this->getId(),
		]) as $userRole) {
			$userRole->delete();
		}

		return true;
	}

	public function addUserPermission($permission)
	{
		return \App\App::getUserPermissionModelClass()->getName()::make($this, $permission);
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
		foreach (\App\App::getUserPermissionModelClass()->getName()::getBy([
			"userId" => $this->getId(),
		]) as $userPermission) {
			$userPermission->delete();
		}

		return true;
	}

	public static function currentHasPermission(\Slim\Http\Request $request, string $permission): bool
	{
		$user = static::getFromRequest($request);
		if (!$user) {
			return false;
		}

		return $user->hasPermission($permission);
	}

	public function getRolePermissions()
	{
		$userRoleClass = \App\App::getUserRoleModelClass()->getName();
		$rolePermissionClass = \App\App::getRolePermissionModelClass()->getName();

		$sql = (SX::select($rolePermissionClass::getColumn("permission")))
			->from($rolePermissionClass::getTable())
			->joinColumns($rolePermissionClass::getColumn("roleId"), $userRoleClass::getColumn("roleId"))
			->where(SX::eq($userRoleClass::getColumn("userId"), $this->getId()))
			->groupBy(SX::groupBy($rolePermissionClass::getColumn("permission")))
			;

		return static::getConnection()->select($sql)->getResult()->getColumnValues("permission");
	}

	public function getUserPermissions()
	{
		return \App\App::getUserPermissionModelClass()->getName()::getBy([
			"userId" => $this->getId(),
		])->getColumnValues("permission");
	}

	public function getAllPermissions(): array
	{
		return \Katu\Cache\Runtime::get(new TIdentifier("users", $this->getId(), "allPermissions"), function () {
			return array_filter(array_unique(array_merge((array)$this->getRolePermissions(), (array)$this->getUserPermissions())));
		});
	}

	public function hasPermission(): bool
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

	public function hasRolePermission($permission): bool
	{
		return in_array($permission, $this->getRolePermissions());
	}

	public function hasUserPermission($permission): bool
	{
		return in_array($permission, $this->getUserPermissions());
	}

	public function setUserSetting($name, $value)
	{
		return \App\App::getUserSettingModelClass()->getName()::getOrCreate($this, $name, $value);
	}

	public function getUserSetting($name)
	{
		return \App\App::getUserSettingModelClass()->getName()::getOneBy([
			"userId" => $this->getId(),
			"name" => $name,
		]);
	}

	public function getUserSettingValue(string $name)
	{
		$userSetting = $this->getUserSetting($name);

		return $userSetting ? $userSetting->getValue() : null;
	}
}
