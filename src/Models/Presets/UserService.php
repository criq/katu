<?php

namespace Katu\Models\Presets;

use Katu\Tools\Users\UserServiceInterface;

/**
 * @deprecated Use UserServiceInterface instead. This class is kept for backward compatibility.
 */
abstract class UserService extends \Katu\Models\Model implements UserServiceInterface
{
	const TABLE = "user_services";

	public static function create(UserInterface $user, string $serviceName, string $serviceUserId): UserServiceInterface
	{
		return static::insert([
			"timeCreated" => new \Katu\Tools\Calendar\Time,
			"userId" => $user->getId(),
			"serviceName" => (string)$serviceName,
			"serviceUserId" => (string)$serviceUserId,
		]);
	}

	public static function getByServiceAndId(string $serviceName, string $serviceUserId): ?\Katu\PDO\Result
	{
		return static::getBy([
			"serviceName" => (string)$serviceName,
			"serviceUserId" => (string)$serviceUserId,
		]);
	}

	public static function getOneByServiceAndId(string $serviceName, string $serviceUserId)
	{
		return static::getByServiceAndId($serviceName, $serviceUserId)->getOne();
	}

	public function getUser(): UserInterface
	{
		$class = \App\App::getContainer()->get(\Katu\Models\Presets\User::class);

		return $class::get($this->userId);
	}

	public function setServiceAccessToken($serviceAccessToken)
	{
		$this->serviceAccessToken = $serviceAccessToken;

		return true;
	}
}
