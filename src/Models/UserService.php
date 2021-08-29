<?php

namespace Katu\Models;

use \Katu\Exception;

class UserService extends \Katu\Model {

	const TABLE = 'user_services';

	static function create($user, $serviceName, $serviceUserId) {
		return static::insert([
			'timeCreated'   => (string)(new \Katu\Utils\DateTime),
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
		return \App\Models\User::get($this->userId);
	}

	public function setServiceAccessToken($serviceAccessToken) {
		$this->update('serviceAccessToken', $serviceAccessToken);

		return true;
	}

}
