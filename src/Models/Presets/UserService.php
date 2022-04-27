<?php

namespace Katu\Models\Presets;

class UserService extends \Katu\Models\Model
{
	const DATABASE = "app";
	const TABLE = "user_services";

	public static function create(User $user, string $serviceName, string $serviceUserId): UserService
	{
		return static::insert([
			"timeCreated" => new \Katu\Tools\Calendar\Time,
			"userId" => $user->getId(),
			"serviceName" => (string)$serviceName,
			"serviceUserId" => (string)$serviceUserId,
		]);
	}

	public static function getByServiceAndId(string $serviceName, string $serviceUserId): ?UserService
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

	public function getUser(): User
	{
		return \App\App::getUserModelClass()->getName()::get($this->userId);
	}

	public function setServiceAccessToken($serviceAccessToken)
	{
		$this->serviceAccessToken = $serviceAccessToken;

		return true;
	}
}
