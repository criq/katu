<?php

namespace Katu\Models\Presets;

class UserService extends \Katu\Models\Model {

	const TABLE = 'user_services';

	static function getUserClass() {
		return '\\Katu\\Models\\Presets\\User';
	}

	static function create($user, $serviceName, $serviceUserId) {
		return static::insert([
			'timeCreated'   => (string)(new \Katu\Tools\DateTime\DateTime),
			'userId'        => (int)$user->getId(),
			'serviceName'   => (string)$serviceName,
			'serviceUserId' => (string)$serviceUserId,
		]);
	}

	static function getByServiceAndId($serviceName, $serviceUserId) {
		return static::getBy([
			'serviceName'   => (string)$serviceName,
			'serviceUserId' => (string)$serviceUserId,
		]);
	}

	static function getOneByServiceAndId($serviceName, $serviceUserId) {
		return static::getByServiceAndId($serviceName, $serviceUserId)->getOne();
	}

	public function getUser() {
		$class = static::getUserClass();

		return $class::get($this->userId);
	}

	public function setServiceAccessToken($serviceAccessToken) {
		$this->update('serviceAccessToken', $serviceAccessToken);

		return true;
	}

}
