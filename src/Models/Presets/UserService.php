<?php

namespace Katu\Models\Presets;

class UserService extends \Katu\Models\Model
{
	const TABLE = 'user_services';

	public static function getUserClass()
	{
		return new \ReflectionClass("\Katu\Models\Presets\User");
	}

	public static function create(User $user, string $serviceName, string $serviceUserId)
	{
		return static::insert([
			'timeCreated' => new \Katu\Tools\DateTime\DateTime,
			'userId' => $user->getId(),
			'serviceName' => (string)$serviceName,
			'serviceUserId' => (string)$serviceUserId,
		]);
	}

	public static function getByServiceAndId(string $serviceName, string $serviceUserId)
	{
		return static::getBy([
			'serviceName' => (string)$serviceName,
			'serviceUserId' => (string)$serviceUserId,
		]);
	}

	public static function getOneByServiceAndId(string $serviceName, string $serviceUserId)
	{
		return static::getByServiceAndId($serviceName, $serviceUserId)->getOne();
	}

	public function getUser()
	{
		return static::getUserClass()->getName()::get($this->userId);
	}

	public function setServiceAccessToken($serviceAccessToken)
	{
		$this->update('serviceAccessToken', $serviceAccessToken);

		return true;
	}
}
