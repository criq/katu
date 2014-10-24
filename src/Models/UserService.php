<?php

namespace Katu\Models;

use \Katu\Exception;

class UserService extends \Katu\Model {

	const TABLE = 'user_services';

	static function create($user, $serviceName, $serviceUserId) {
		return static::insert(array(
			'timeCreated'   => (string) (\Katu\Utils\DateTime::get()->getDBDatetimeFormat()),
			'userId'        => (int)    ($user->id),
			'serviceName'   => (string) ($serviceName),
			'serviceUserId' => (string) ($serviceUserId),
		));
	}

	static function getByServiceAndID($serviceName, $serviceUserId) {
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

		return TRUE;
	}

}
