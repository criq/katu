<?php

namespace Katu\Models;

use \Katu\Exception;

class UserService extends \Katu\Model {

	const DATABASE = 'app';
	const TABLE = 'user_services';

	static function create($user, $serviceName, $serviceUserId) {
		return static::insert(array(
			'timeCreated'   => (string) (\Katu\Utils\DateTime::get()->getDbDateTimeFormat()),
			'userId'        => (int)    ($user->getId()),
			'serviceName'   => (string) ($serviceName),
			'serviceUserId' => (string) ($serviceUserId),
		));
	}

	static function getByServiceAndId($serviceName, $serviceUserId) {
		return static::getBy(array(
			'serviceName'   => (string) ($serviceName),
			'serviceUserId' => (string) ($serviceUserId),
		));
	}

	public function getUser() {
		return \App\Models\User::get($this->userId);
	}

	public function setServiceAccessToken($serviceAccessToken) {
		$this->update('serviceAccessToken', $serviceAccessToken);

		return true;
	}

}
