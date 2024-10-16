<?php

namespace Katu\Models\Presets;

use Katu\Tools\Calendar\Time;
use Katu\Tools\Cookies\Cookie;
use Katu\Tools\Security\EncodedPassword;
use Katu\Tools\Security\PlainPassword;
use Katu\Types\TIdentifier;
use Psr\Http\Message\ServerRequestInterface;
use Sexy\Sexy as SX;

abstract class User extends \Katu\Models\Model
{
	const TABLE = "users";

	public $id;
	public $name;
	public $password;

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
			$accessTokenClass = \App\App::getContainer()->get(\Katu\Models\Presets\AccessToken::class);
			$accessToken = $accessTokenClass::getValidFromRequest($request);
			if ($accessToken) {
				// Extend access token.
				$accessToken->extendTimeExpires($accessTokenClass::generateTimeExpires());
				$accessToken->persist();

				// Save extended cookie.
				$accessToken->getCookie()->persist();

				return $accessToken->getUser();
			}
		}

		return null;
	}

	/****************************************************************************
	 * Getters & Setters.
	 */
	public function setName(string $name): User
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
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\EmailAddress::class);

		if (!$emailAddress || !($emailAddress instanceof $class)) {
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

		$this->{static::$columnNames["emailAddressId"]} = $emailAddress->getId();

		return true;
	}

	public function getEmailAddress(): ?EmailAddress
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\EmailAddress::class);

		return $class::get($this->{static::$columnNames["emailAddressId"]});
	}

	public function setPassword(?string $password): User
	{
		$this->password = $password;

		return $this;
	}

	public function getPassword(): ?string
	{
		return $this->password;
	}

	public function setPlainPassword(PlainPassword $plainPassword): User
	{
		$plainPassword
			->setSaltLength(128)
			->setIterations(10)
			;

		$encodedPassword = $plainPassword->getEncodedPassword();

		$this->setPassword($encodedPassword);

		return $this;
	}

	public function getEncodedPassword(): ?EncodedPassword
	{
		return new EncodedPassword($this->getPassword());
	}

	public function hasEncodedPassword(): bool
	{
		return (bool)$this->getPassword();
	}

	public function createAccessToken(): AccessToken
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\AccessToken::class);

		return $class::create($this);
	}

	public function getOrCreateSafeAccessToken(): AccessToken
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\AccessToken::class);

		return $class::getOrCreateSafe($this);
	}

	public function getAccessTokens(): \Katu\PDO\Result
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\AccessToken::class);

		return $class::getBy([
			"userId" => $this->getId(),
		]);
	}

	public function addUserService($serviceName, $serviceUserId)
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\UserService::class);

		return $class::create($this, $serviceName, $serviceUserId);
	}

	public function makeUserService($serviceName, $serviceUserId)
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\UserService::class);

		return $class::upsert([
			"userId" => $this->getId(),
			"serviceName" => (string)$serviceName,
			"serviceUserId" => (string)$serviceUserId,
		], [
			"timeCreated" => new Time,
		]);
	}

	public function getDefaultUserServiceByName($serviceName)
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\UserService::class);

		return $class::getOneBy([
			"userId" => $this->getId(),
			"serviceName" => (string)$serviceName,
		]);
	}

	public function hasEmailAddress()
	{
		return (bool) $this->{static::$columnNames["emailAddressId"]};
	}

	public function login(): User
	{
		$accessToken = $this->createAccessToken();
		$cookie = $accessToken->getCookie();
		$cookie->persist();

		return $this;
	}

	public static function logout(): bool
	{
		(new Cookie("accessToken"))->expire();

		return true;
	}

	public function addRole($role)
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\UserRole::class);

		return $class::make($this, $role);
	}

	public function addRolesByIds($roleIds)
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\Role::class);

		$roles = [];
		foreach ((array) $roleIds as $roleId) {
			$role = $class::get($roleId);
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

	public function hasRole(Role $role): bool
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\UserRole::class);

		return (bool)$class::getOneBy([
			"userId" => $this->getId(),
			"roleId" => $role->getId(),
		]);
	}

	public function getUserRoles()
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\UserRole::class);

		return $class::getBy([
			"userId" => $this->getId(),
		]);
	}

	public function deleteAllRoles()
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\UserRole::class);

		foreach ($class::getBy([
			"userId" => $this->getId(),
		]) as $userRole) {
			$userRole->delete();
		}

		return true;
	}

	public function addUserPermission($permission)
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\UserPermission::class);

		return $class::make($this, $permission);
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
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\UserPermission::class);

		foreach ($class::getBy([
			"userId" => $this->getId(),
		]) as $userPermission) {
			$userPermission->delete();
		}

		return true;
	}

	public static function currentHasPermission(ServerRequestInterface $request, string $permission): bool
	{
		$user = static::getFromRequest($request);
		if (!$user) {
			return false;
		}

		return $user->hasPermission($permission);
	}

	public function getRolePermissions(): array
	{
		$userRoleClass = \App\App::getContainer()->get(\Katu\Models\Presets\UserRole::class);
		$rolePermissionClass = \App\App::getContainer()->get(\Katu\Models\Presets\RolePermission::class);

		$sql = (SX::select($rolePermissionClass::getColumn("permission")))
			->from($rolePermissionClass::getTable())
			->joinColumns($rolePermissionClass::getColumn("roleId"), $userRoleClass::getColumn("roleId"))
			->where(SX::eq($userRoleClass::getColumn("userId"), $this->getId()))
			->groupBy(SX::groupBy($rolePermissionClass::getColumn("permission")))
			;

		return static::getConnection()->select($sql)->getResult()->getColumnValues("permission");
	}

	public function getUserPermissions(): array
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\UserPermission::class);

		return $class::getBy([
			"userId" => $this->getId(),
		])->getColumnValues("permission");
	}

	public function getResolvedPermissions(): array
	{
		return \Katu\Cache\Runtime::get(new TIdentifier(__CLASS__, __FUNCTION__, $this->getId()), function () {
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
			$status[$permission] = in_array($permission, $this->getResolvedPermissions());
		}

		if ($any) {
			return in_array(true, $status);
		}

		return !in_array(false, $status);
	}

	public function hasRolePermission(string $permission): bool
	{
		return in_array($permission, $this->getRolePermissions());
	}

	public function hasUserPermission(string $permission): bool
	{
		return in_array($permission, $this->getUserPermissions());
	}

	public function setUserSetting(string $name, $value)
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\UserSetting::class);

		return $class::getOrCreate($this, $name, $value);
	}

	public function getUserSetting(string $name)
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\UserSetting::class);

		return $class::getOneBy([
			"userId" => $this->getId(),
			"name" => $name,
		]);
	}

	public function getUserSettingValue(string $name): ?string
	{
		$userSetting = $this->getUserSetting($name);

		return $userSetting ? $userSetting->getValue() : null;
	}
}
